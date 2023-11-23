<?php

namespace App\Console\Commands;

use App\Enums\ExchangeService;
use App\Enums\HeartbeatServiceName;
use App\Enums\HeartbeatStatus;
use App\Jobs\HeartbeatStatusJob;
use App\Models\ExchangeUserPairs;
use App\Models\Service;
use App\Models\ServiceLogLaunch;
use App\Services\Exchange\ExchangeManagerInterface;
use Illuminate\Console\Command;

class ExchangeCommand extends Command
{
    private ServiceLogLaunch $serviceLogLaunch;
    private Service $service;

    protected $signature = 'exchange';

    protected $description = 'Command description';

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

        $pairs = ExchangeUserPairs::where('exchange_id',  ExchangeService::Huobi->getId())->get();
        $exchange = ExchangeService::tryFrom('huobi');

        foreach ($pairs as $pair) {
            $exchangeService = $this->exchangeManager->make($exchange, $pair->user);
            $result = $exchangeService->exchange($pair);
            HeartbeatStatusJob::dispatch(
                service: $this->service,
                status: HeartbeatStatus::InProgress,
                message: 'Success exchange :reasonText.',
                messageVariable: ['reasonText' => $result],
                serviceLogLaunch: $this->serviceLogLaunch,
            );
        }

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
            message: 'Start Exchange',
            serviceLogLaunch: $this->serviceLogLaunch,
        );
    }
}
