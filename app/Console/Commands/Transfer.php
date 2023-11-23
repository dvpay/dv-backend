<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\HeartbeatServiceName;
use App\Enums\HeartbeatStatus;
use App\Jobs\HeartbeatStatusJob;
use App\Models\Currency;
use App\Models\Service;
use App\Models\ServiceLogLaunch;
use App\Models\Wallet;
use App\Services\Processing\Contracts\TransferContract;
use App\Services\Withdrawal\WithdrawalRuleManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 *
 */
class Transfer extends Command
{
    /**
     * @var string
     */
    protected $signature = 'transfer {--force}';

    /**
     * @var ServiceLogLaunch
     */
    private ServiceLogLaunch $serviceLogLaunch;
    /**
     * @var Service
     */
    private Service $service;

    /**
     * @param WithdrawalRuleManager $ruleManager
     * @param TransferContract $contract
     * @return void
     * @throws Throwable
     */
    public function handle(WithdrawalRuleManager $ruleManager, TransferContract $contract)
    {
        $this->initMonitor();

        HeartbeatStatusJob::dispatch(
            service: $this->service,
            status: HeartbeatStatus::InProgress,
            message: 'Start Withdrawal',
            serviceLogLaunch: $this->serviceLogLaunch,
        );

        try {
            $time = time();

            $ignoreRules = (bool)$this->option('force');

            $wallets = Wallet::where('withdrawal_enabled', true)->get();

            foreach ($wallets as $wallet) {
                $user = $wallet->user;
                if (!$user || $user->hasPermissionTo('transfer funds')) {
                    continue;
                }

                $can = true;

                if (!$ignoreRules) {
                    $can = $ruleManager->execute($wallet);
                }

                if (!$can) {
                    continue;
                }

                $ownerId = $user->processing_owner_id;

                $currencies = Currency::where([
                    ['blockchain', $wallet->blockchain],
                    ['has_balance', true],
                ])->get();
                foreach ($currencies as $currency) {

                    Log::error('Sending a withdrawal request', ['ownerId' => $ownerId, 'walletBlockchain' => $wallet->blockchain]);

                    $contract->doTransfer(
                        $ownerId,
                        $wallet->blockchain,
                        false,
                        $currency->contract_address
                    );
                }
            }

            HeartbeatStatusJob::dispatch(
                service: $this->service,
                status: HeartbeatStatus::Up,
                message: 'Success Withdrawal',
                serviceLogLaunch: $this->serviceLogLaunch,
            );

            $this->info('The command was successful! ' . time() - $time . ' s.');
        } catch (Throwable $e) {
            HeartbeatStatusJob::dispatch(
                service: $this->service,
                status: HeartbeatStatus::Down,
                message: 'Service is down. Reason: :reasonText.',
                messageVariable: ['reasonText' => $e->getMessage()],
                serviceLogLaunch: $this->serviceLogLaunch,
            );

            throw $e;
        }
    }

    protected function initMonitor():void
    {
        $this->service = Service::where('slug', HeartbeatServiceName::CronWithdrawal)
            ->first();

        $this->serviceLogLaunch = ServiceLogLaunch::create([
            'service_id' => $this->service->id,
            'status'     => HeartbeatStatus::InProgress
        ]);

    }
}
