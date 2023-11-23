<?php

declare(strict_types=1);

namespace App\Services\Report;

use App\Enums\InvoiceStatus;
use App\Enums\TransactionType;
use App\Helpers\CommissionCalculation;
use App\Models\Currency;
use App\Models\Invoice;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Balance\BalanceService;
use App\Services\Processing\Contracts\ProcessingWalletContract;
use DateInterval;
use DatePeriod;
use DateTime;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Collection;
use Psr\SimpleCache\InvalidArgumentException;

readonly class ReportService
{
    public function __construct(
        private BalanceService           $balanceService,
        private ProcessingWalletContract $processingWalletService
    )
    {
    }

    public static function make(...$params): static
    {
        return new static(...$params);
    }

    public function statsByUser(array $period, User $user): array
    {
        $sumInvoice = Transaction::whereBetween('created_at', $period)
            ->where('user_id', $user->id)
            ->where('type', TransactionType::Invoice->value)
            ->groupBy('currency_id')
            ->selectRaw('currency_id, SUM(amount) as total_amount')
            ->get();

        $invoiceStats = Invoice::whereBetween('created_at', $period)
            ->whereIn('store_id', $user->storesHolder->pluck('id'))
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('SUM(CASE WHEN status = ? OR status = ? THEN 1 ELSE 0 END) as paid', [
                InvoiceStatus::Paid->value,
                InvoiceStatus::PartiallyPaid->value
            ])
            ->first();

        $sumTransfers = Transaction::whereBetween('created_at', $period)
            ->where('user_id', $user->id)
            ->selectRaw('COUNT(*) as count, currency_id')
            ->selectRaw('SUM(CASE WHEN type = ? THEN 1 ELSE 0 END) as transfer', [
                TransactionType::Transfer->value
            ])
            ->groupBy('currency_id')
            ->get();

        $savedOnCommission = 0;

        foreach ($sumInvoice as $value) {
            $savedOnCommission += CommissionCalculation::savedOnCommission(
                $value->currency_id,
                $value->total_amount,
                $sumTransfers->firstWhere('currency_id', $value->currency_id)->transfer);
        }


        $storesStat = $user->storesHolder()
            ->withSum('invoicesSuccess', 'amount')
            ->withCount('invoicesSuccess')
            ->get();

        return [
            'sum'               => $sumInvoice->sum('total_amount'),
            'sumTransfer'       => $sumTransfers->sum('transfer'),
            'invoice'           => [
                'count' => $invoiceStats->count ?? 0,
                'paid'  => $invoiceStats->paid ?? 0
            ],
            'savedOnCommission' => $savedOnCommission,
            'storesStat'        => $storesStat,
        ];
    }

    /**
     * @throws InvalidArgumentException
     */
    public function balanceByUser(User $user): array
    {
        $todaySum = Transaction::where('user_id', $user->id)
            ->whereDate('created_at', now()->today())
            ->where('type', TransactionType::Invoice->value)
            ->sum('amount');

        $yesterdaySum = Transaction::where('user_id', $user->id)
            ->whereDate('created_at', now()->yesterday())
            ->where('type', TransactionType::Invoice->value)
            ->sum('amount');

        $invoiceCount = Invoice::whereDate('created_at', now()->today())
            ->whereIn('store_id', $user->storesHolder->pluck('id'))
            ->count();

        $transactionCount = Transaction::where('user_id', $user->id)
            ->whereDate('created_at', now()->today())
            ->where('type', TransactionType::Invoice->value)
            ->count();

        $hotWalletsBalance = $this->balanceService->getAllBalanceFromProcessing($user);

        $processingWallets = $this->processingWalletService->getWallets($user->processing_owner_id);

        return [
            'todaySum'          => $todaySum,
            'yesterdaySum'      => $yesterdaySum,
            'invoiceCount'      => $invoiceCount,
            'transactionCount'  => $transactionCount,
            'balanceHotWallet'  => collect($hotWalletsBalance),
            'processingWallets' => $processingWallets
        ];
    }

    /**
     * @param Currency $currency
     * @param User|Authenticatable $user
     * @return array
     * @throws \Exception
     */
    public function transferStatsByUserAndCurrency(Currency $currency, User|Authenticatable $user): array
    {
        date_default_timezone_set($user->location ?? 'UTC');

        $dateEnd = new DateTime('today 23:59');
        $dateStart = new DateTime('today 00:00');
        $dateStart->sub(new DateInterval('P5D'));

        $period = new DatePeriod(
            $dateStart,
            new DateInterval('P1D'),
            $dateEnd
        );

        $summary = [];

        foreach ($period as $value) {
            $item = [
                'date'            => $value->format(DATE_ATOM),
                'total_energy'    => '0',
                'total_bandwidth' => '0',
                'count'           => '0',
            ];
            $summary = [$item, ...$summary];
        }

        $transactions = $this->getResourceInfo($user, $dateStart, $currency);

        foreach ($transactions as $transaction) {
            foreach ($summary as $key => $value) {
                if ($value['date'] == (new DateTime($transaction->created_date))->format(DATE_ATOM)) {
                    $summary[$key] = [
                        'date'            => (new DateTime($transaction->created_date))->format(DATE_ATOM),
                        'total_energy'    => $transaction->total_energy,
                        'total_bandwidth' => $transaction->total_bandwidth,
                        'count'           => $transaction->count,
                    ];
                }
            }
        }
        return $summary;
    }

    /**
     * @param User $user
     * @param DateTime $dateStart
     * @param Currency $currency
     * @return Collection
     */
    private function getResourceInfo(User $user, DateTime $dateStart, Currency $currency): Collection
    {
        $timezone = $user->location ?? '+00:00';

        return Transaction::selectRaw("
            DATE(CONVERT_TZ(created_at, '+00:00', '$timezone')) as created_date,
            COUNT(*) as count, 
            SUM(energy) as total_energy, 
            SUM(bandwidth) as total_bandwidth
        ")
            ->where('user_id', $user->id)
            ->where('type', TransactionType::Transfer)
            ->where('currency_id', $currency->id)
            ->whereRaw("DATE(CONVERT_TZ(created_at, '+00:00', '$timezone')) >= {$dateStart->format('Y-m-d')}")
            ->groupBy('created_date')
            ->orderBy('created_date', 'desc')
            ->get();
    }

    /**
     * @param Currency $currency
     * @param User|Authenticatable $user
     * @param DateTime $date
     * @return Collection
     */
    public function getTransferByDate(Currency $currency, User|Authenticatable $user, DateTime $date): Collection
    {
        return Transaction::where('user_id', $user->id)
            ->where('type', TransactionType::Transfer)
            ->where('currency_id', $currency->id)
            ->whereDate("created_at", $date->format('Y-m-d'))
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * @param Currency $currency
     * @param User|Authenticatable $user
     * @return array
     */
    public function getTransferStatByDate(Currency $currency, User|Authenticatable $user): array
    {
        $dateStart = now()->subMonth()->format('Y-m-d');
        $result = Transaction::selectRaw('count(*) as count, AVG(bandwidth) as avg_bandwidth, AVG(energy) as avg_energy')
            ->where('type', TransactionType::Transfer)
            ->where('user_id', $user->id)
            ->where('currency_id', $currency->id)
            ->whereDate('created_at', '>=', $dateStart)
            ->first();

        return [
            'avgTransfer'  => round($result->count / 30),
            'avgBandwidth' => $result->avg_bandwidth,
            'avgEnergy'    => $result->avg_energy,
        ];
    }
}
