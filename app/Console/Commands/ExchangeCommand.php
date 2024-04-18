<?php

namespace App\Console\Commands;

use App\Enums\ExchangeService;
use App\Enums\HeartbeatServiceName;
use App\Enums\HeartbeatStatus;
use App\Enums\PermissionsEnum;
use App\Jobs\HeartbeatStatusJob;
use App\Models\ExchangeUserPairs;
use App\Models\Service;
use App\Models\ServiceLogLaunch;
use App\Services\Exchange\ExchangeManager;
use Illuminate\Console\Command;

class ExchangeCommand extends Command
{
    private ServiceLogLaunch $serviceLogLaunch;
    private Service $service;

    protected $signature = 'exchange';

    protected $description = 'Command description';

    public function __construct(
        private readonly ExchangeManager $exchangeManager,
    )
    {
        parent::__construct();
    }


    public function handle(): void
    {
        $this->initMonitor();
        $time = time();

        $pairs = ExchangeUserPairs::where('exchange_id',  ExchangeService::Huobi->getId())->get();

        foreach ($pairs as $pair) {
            if ($pair->user->hasPermissionTo(PermissionsEnum::ExchangeStop->value)) {
                continue;
            }
            $exchangeService = $this->exchangeManager
                ->setUser($pair->user)
                ->driver('huobi');

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
