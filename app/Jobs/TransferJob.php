<?php

namespace App\Jobs;

use App\Enums\HeartbeatServiceName;
use App\Enums\HeartbeatStatus;
use App\Enums\WithdrawalInterval;
use App\Models\Currency;
use App\Models\Service;
use App\Models\ServiceLogLaunch;
use App\Models\Wallet;
use App\Services\Processing\Contracts\TransferContract;
use App\Services\Withdrawal\WithdrawalRuleManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use JetBrains\PhpStorm\Deprecated;
use function Clue\StreamFilter\fun;

#[Deprecated] // After test delete and make new
class TransferJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private ServiceLogLaunch $serviceLogLaunch;
    /**
     * @var Service
     */
    private Service $service;

    public function __construct(private readonly WithdrawalInterval $interval)
    {
    }

    public function handle(WithdrawalRuleManager $ruleManager, TransferContract $contract): void
    {
        $this->initMonitor();

        HeartbeatStatusJob::dispatch(
            service: $this->service,
            status: HeartbeatStatus::InProgress,
            message: 'Start Withdrawal',
            serviceLogLaunch: $this->serviceLogLaunch,
        );

        Wallet::where('withdrawal_interval_cron', $this->interval->name)
            ->where('withdrawal_enabled', true)
            ->get()
            ->each(fn($wallet) => $this->walletTransfer($wallet, $ruleManager, $contract));

        HeartbeatStatusJob::dispatch(
            service: $this->service,
            status: HeartbeatStatus::Up,
            message: 'Success Withdrawal',
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
    private function walletTransfer(Wallet $wallet, WithdrawalRuleManager $ruleManager, TransferContract $contract): void
    {
        $user = $wallet->user;
        if (!$user || $user->hasPermissionTo('transfer funds')) {
            return;
        }

        if (!$ruleManager->execute($wallet)) {
            return;
        }

        $currencies = Currency::where([
            ['blockchain', $wallet->blockchain],
            ['has_balance', true],
        ])->get();

        foreach ($currencies as $currency) {
            Log::error('Sending a withdrawal request', ['ownerId' => $user->processing_owner_id, 'walletBlockchain' => $wallet->blockchain]);
            $contract->doTransfer(
                owner: $user->processing_owner_id,
                blockchain: $wallet->blockchain,
                isManual: false,
                address: $wallet->address,
                contract: $currency->contract_address,
            );
        }


    }
}
