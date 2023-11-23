<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\HeartbeatServiceName;
use App\Enums\HeartbeatStatus;
use App\Enums\RateSource;
use App\Jobs\HeartbeatStatusJob;
use App\Models\Service;
use App\Models\ServiceLogLaunch;
use App\Services\Currency\CurrencyRateService;
use Illuminate\Console\Command;
use Throwable;

class CacheCurrencyRates extends Command
{
    private ServiceLogLaunch $serviceLogLaunch;
    private Service $service;

    public function __construct(private readonly CurrencyRateService $currencyService)
    {
        parent::__construct();
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "cache:currency:rate {rateSourceName?}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set currency rates from rate sources.';

    /**
     * Execute the console command.
     *
     * @return void
     * @throws Throwable
     */
    public function handle(): void
    {
        $this->initMonitor();

        HeartbeatStatusJob::dispatch(
            service: $this->service,
            status: HeartbeatStatus::InProgress,
            message: 'Start Update Rate',
            serviceLogLaunch: $this->serviceLogLaunch,
        );

        try {
            $time = time();

            $rateSourceName = $this->argument('rateSourceName') ?? '';
            $rateSource = RateSource::tryFrom($rateSourceName);

            $this->currencyService->loadCurrencyRate($rateSource);

            HeartbeatStatusJob::dispatch(
                service: $this->service,
                status: HeartbeatStatus::Up,
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
    protected function initMonitor(): void
    {
        $this->service = Service::where('slug', HeartbeatServiceName::CronCacheCurrencyRate)
            ->first();

        $this->serviceLogLaunch = ServiceLogLaunch::create([
            'service_id' => $this->service->id,
            'status'     => HeartbeatStatus::InProgress
        ]);
    }
}