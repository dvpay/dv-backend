<?php

declare(strict_types=1);

namespace App\Services\Withdrawal;

use App\Dto\CreateWithdrawalDto;
use App\Dto\WithdrawalListDto;
use App\Dto\WithdrawalStatsDto;
use App\Enums\TransactionType;
use App\Models\Currency;
use App\Models\Transaction;
use App\Repositories\WalletRepository;
use App\Services\Processing\Contracts\TransferContract;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Authenticatable;
use JetBrains\PhpStorm\Deprecated;
use Psr\Http\Message\ResponseInterface;
use Throwable;

#[Deprecated]
class WithdrawalService
{
    public function __construct(
        private readonly WalletRepository $walletRepository,
        private readonly TransferContract $transferContract
    )
    {
    }

    /**
     * @throws Throwable
     */
    public function withdrawal(CreateWithdrawalDto $dto)
    {
        if (isset($dto->currencyId)) {
            return $this->withdrawalOneCurrency($dto);
        } else {
            $this->withdrawalAllCurrencies($dto);
        }
    }

    private function withdrawalOneCurrency(CreateWithdrawalDto $dto): ResponseInterface
    {
        $currency = Currency::find($dto->currencyId);

        return $this->sendWithdrawal($dto, $currency);
    }

    private function withdrawalAllCurrencies(CreateWithdrawalDto $dto): void
    {
        $wallets = $this->walletRepository->getWalletsByUserId($dto->user->id);

        foreach ($wallets as $wallet) {
            $currencies = Currency::where([
                ['is_fiat', false],
                ['has_balance', true],
                ['blockchain', $wallet->blockchain],
            ])->get();
            foreach ($currencies as $currency) {
                try {
                    $this->sendWithdrawal($dto, $currency);
                } catch (Throwable) {
                    continue;
                }
            }
        }
    }

    private function sendWithdrawal(CreateWithdrawalDto $dto, Currency $currency): ResponseInterface
    {
        return $this->transferContract->doTransfer(
            $dto->user->processing_owner_id,
            $currency->blockchain,
            $dto->isManual,
            $currency->contract_address
        );
    }

    public function withdrawalList(WithdrawalListDto $dto)
    {
        $timezone = $dto->user->location ?? '+00:00';

        $listQuery = Transaction::select('tx_id', 'currency_id', 'to_address', 'amount', 'created_at', 'withdrawal_is_manual')
            ->where([
                ['user_id', $dto->user->id],
                ['type', TransactionType::Transfer],
            ])
            ->orderBy($dto->sortField, $dto->sortDirection);

        if ($dto->dateFrom) {
            $dateFrom = Carbon::create($dto->dateFrom);
            $listQuery->whereRaw("DATE(CONVERT_TZ(created_at, '+00:00', '$timezone')) >= '$dateFrom'");
        }
        if ($dto->dateTo) {
            $dateTo = Carbon::create($dto->dateTo);
            $listQuery->whereRaw("DATE(CONVERT_TZ(created_at, '+00:00', '$timezone')) <= '$dateTo'");
        }

        return $listQuery->paginate($dto->perPage);
    }

    public function withdrawalStats(Authenticatable $user): array
    {
        $timezone = $user->location ?? '+00:00';
        $partQuery = "CONVERT_TZ(created_at, '+00:00', '$timezone')";

        $baseQuery = Transaction::selectRaw('SUM(amount_usd) AS sum')
            ->where([
                ['user_id', '=', $user->id],
                ['type', '=', TransactionType::Transfer],
            ]);

        $sumToday = $baseQuery->clone()->whereRaw("DATE($partQuery) = CURRENT_DATE")->first();
        $sumYesterday = $baseQuery->clone()->whereRaw("DATE($partQuery) = CURRENT_DATE - INTERVAL 1 DAY")->first();
        $sumCurrentMonth = $baseQuery->clone()
            ->whereRaw("YEAR($partQuery) = YEAR(CURRENT_DATE)")
            ->whereRaw("MONTH($partQuery) = MONTH(CURRENT_DATE)")
            ->first();
        $sumPreviousMonth = $baseQuery->clone()
            ->whereRaw("YEAR($partQuery) = YEAR(CURRENT_DATE - INTERVAL 1 MONTH)")
            ->whereRaw("MONTH($partQuery) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH)")
            ->first();

        return [
            'today'         => ['amountUsd' => $sumToday?->sum ?: '0'],
            'yesterday'     => ['amountUsd' => $sumYesterday?->sum ?: '0'],
            'currentMonth'  => ['amountUsd' => $sumCurrentMonth?->sum ?: '0'],
            'previousMonth' => ['amountUsd' => $sumPreviousMonth?->sum ?: '0'],
        ];
    }

    public function withdrawalDates(WithdrawalStatsDto $dto, Authenticatable $user)
    {
        $timezone = $user->location ?? '+00:00';

        return Transaction::selectRaw(
            "DATE(CONVERT_TZ(created_at, '+00:00', '$timezone')) AS date,"
            . " SUM(amount_usd) AS amountUsd,"
            . " COUNT(1) AS transactionCount"
        )
            ->where([
                ['user_id', '=', $user->id],
                ['type', '=', TransactionType::Transfer],
            ])
            ->groupBy('date')
            ->orderBy($dto->sortField, $dto->sortDirection)
            ->paginate($dto->perPage);
    }
}
