<?php

namespace App\Jobs;

use App\Enums\CurrencySymbol;
use App\Enums\HeartbeatServiceName;
use App\Enums\HeartbeatStatus;
use App\Enums\RateSource;
use App\Enums\WithdrawalInterval;
use App\Models\Service;
use App\Models\ServiceLogLaunch;
use App\Models\User;
use App\Models\WithdrawalWallet;
use App\Services\Currency\CurrencyRateService;
use App\Services\Processing\BalanceGetter;
use App\Services\Processing\TransferService;
use App\Services\WithdrawalWallet\WithdrawalWalletService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class NewTransferJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private ServiceLogLaunch $serviceLogLaunch;
    /**
     * @var Service
     */
    private Service $service;

    private WithdrawalWalletService $withdrawalWalletService;
    private BalanceGetter $balanceGetter;
    private TransferService $transferService;

    public function __construct(
        private readonly WithdrawalInterval $interval
    )
    {
        $this->onQueue('transfer');
    }

    public function handle(
        BalanceGetter $balanceGetter,
        WithdrawalWalletService $withdrawalWalletService,
        TransferService $transferService,
    ): void
    {
        $this->initMonitor();
        $this->balanceGetter = $balanceGetter;
        $this->withdrawalWalletService = $withdrawalWalletService;
        $this->transferService = $transferService;

        HeartbeatStatusJob::dispatch(
            service: $this->service,
            status: HeartbeatStatus::InProgress,
            message: 'Start Withdrawal',
            serviceLogLaunch: $this->serviceLogLaunch,
        );

        WithdrawalWallet::where('withdrawal_enabled', true)
            ->where('withdrawal_interval', $this->interval->name)
            ->get()
            ->each(fn($wallet) => $this->walletTransfer($wallet));

        HeartbeatStatusJob::dispatch(
            service: $this->service,
            status: HeartbeatStatus::Up,
            message: 'Withdrawal success',
            serviceLogLaunch: $this->serviceLogLaunch,
        );

    }

    protected function initMonitor(): void
    {
        $this->service = Service::where('slug', HeartbeatServiceName::CronWithdrawal)
            ->first();

        $this->serviceLogLaunch = ServiceLogLaunch::create([
            'service_id' => $this->service->id,
            'status'     => HeartbeatStatus::InProgress
        ]);
    }

    private function walletTransfer(WithdrawalWallet $withdrawalWallet): void
    {
        $user = $withdrawalWallet->user;

        if (!$user || $user->hasPermissionTo('transfer funds')) {
            return;
        }

        if($this->transferService->getTransferInWorkCountByUser($user) > 0) {
            return;
        }

        if ($this->checkBalance($user, $withdrawalWallet)) {
            $this->withdrawalWalletService->sendWithdrawal($withdrawalWallet, $user);
        }
    }

    private function checkBalance(User $user, WithdrawalWallet $withdrawalWallet): bool
    {
        $balances = $this->balanceGetter->get($user);

        foreach ($balances as $key => $value) {
            if ($key != $withdrawalWallet->blockchain->getShortBlockchain()) {
                continue;
            }
            $currency = Str::upper($withdrawalWallet->currency);
            $rate = $this->getRate(CurrencySymbol::tryFrom($currency));
            if ((float)$value * $rate['rate'] >= (float)$withdrawalWallet->withdrawal_min_balance) {
                return true;
            }
        }
        return false;
    }

    private function getRate(CurrencySymbol $currencyCode): ?array
    {
        $currencyService = app(CurrencyRateService::class);

        return $currencyService->getCurrencyRate(RateSource::Binance, CurrencySymbol::USDT, $currencyCode);
    }
}
