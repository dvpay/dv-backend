<?php

namespace App\Console\Commands;

use App\Enums\ExchangeService;
use App\Enums\HeartbeatServiceName;
use App\Enums\HeartbeatStatus;
use App\Jobs\HeartbeatStatusJob;
use App\Models\ExchangeWithdrawalWallet;
use App\Models\Service;
use App\Models\ServiceLogLaunch;
use App\Services\Exchange\ExchangeManagerInterface;
use Illuminate\Console\Command;

class WithdrawalCommand extends Command
{
    protected $signature = 'withdrawal';

    protected $description = 'Command for withdrawal from exchange';

    private ServiceLogLaunch $serviceLogLaunch;
    private Service $service;
    public function __construct(
        private readonly ExchangeManagerInterface $exchangeManager,
    )
    {
        parent::__construct();
    }


    public function handle(): void
    {
        $this->initMonitor();
        $time = time();
        $exchange = ExchangeService::tryFrom('huobi');

        $exchangeWithdrawalWallets = ExchangeWithdrawalWallet::select('user_id')
            ->where('exchange_id', $exchange->getId())
            ->where('is_withdrawal_enable', true)
            ->distinct()
            ->get();

        foreach ($exchangeWithdrawalWallets as $exchangeWithdrawalWallet) {
            $exchangeService = $this->exchangeManager->make($exchange, $exchangeWithdrawalWallet->user);
            $exchangeService->withdrawalFromExchange();

            HeartbeatStatusJob::dispatch(
                service: $this->service,
                status: HeartbeatStatus::InProgress,
                message: 'Success withdrawal',
                serviceLogLaunch: $this->serviceLogLaunch,
            );
        };

        HeartbeatStatusJob::dispatch(
            service: $this->service,
            status: HeartbeatStatus::Up,
            serviceLogLaunch: $this->serviceLogLaunch,
        );
        $this->info('The command was successful! ' . time() - $time . ' s.');

    }

    protected function initMonitor(): void
    {
        $this->service = Service::where('slug', HeartbeatServiceName::CronExchangeWithdrawal)
            ->first();

        $this->serviceLogLaunch = ServiceLogLaunch::create([
            'service_id' => $this->service->id,
            'status'     => HeartbeatStatus::InProgress
        ]);

        HeartbeatStatusJob::dispatch(
            service: $this->service,
            status: HeartbeatStatus::InProgress,
            message: 'Start withdrawal',
            serviceLogLaunch: $this->serviceLogLaunch,
        );
    }
}
