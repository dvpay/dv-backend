<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\HeartbeatServiceName;
use App\Enums\HeartbeatStatus;
use App\Jobs\HeartbeatStatusJob;
use App\Jobs\TelegramNotificationJob;
use App\Models\Service;
use App\Models\ServiceLogLaunch;
use App\Services\Processing\ProcessingService;
use Exception;
use Illuminate\Console\Command;
use Throwable;

class ProcessingServiceStatusCheck extends Command
{
    private const TIME_BLOCK = 10;
    private const TRON_BLOCK_FAULT = 10;

    /**
     * @var ServiceLogLaunch
     */
    private ServiceLogLaunch $serviceLogLaunch;
    /**
     * @var Service
     */
    private Service $service;

    public function __construct(private readonly ProcessingService $processingService)
    {
        parent::__construct();
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'processing:status:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check processing service status.';

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
            message: 'Start Check Processing status',
            serviceLogLaunch: $this->serviceLogLaunch,
        );

        try {
            $time = time();

            $this->checkProcessingStatus();

            HeartbeatStatusJob::dispatch(
                service: $this->service,
                status: HeartbeatStatus::Up,
                message: 'Processing status success',
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

	/**
	 * @return void
	 * @throws Throwable
	 */
    private function checkProcessingStatus(): void
    {
        $serviceLogLaunch = ServiceLogLaunch::create([
            'service_id' => $this->service->id,
            'status'     => HeartbeatStatus::InProgress
        ]);

        try {
            $status = $this->processingService->getStatusService();
            $status = json_decode($status->getBody()->getContents());

            if (!$status->success) {
                throw new Exception('Processing service response with status false');
            }

            if (isset($status->lastBlockAt) && time() - $status->lastBlockAt > self::TIME_BLOCK * 60) {
                throw new Exception('Processing last block generated more ' . self::TIME_BLOCK . ' minutes ago');
            }

            if($status->nodeStatus->bitcoin->explorerHeight !== $status->nodeStatus->bitcoin->nodeHeight) {
                throw new Exception('BITCOIN discrepancy block height:' . $status->nodeStatus->bitcoin->nodeHeight -  $status->nodeStatus->bitcoin->explorerHeight . ' height');
            }

            if(($status->nodeStatus->tron->nodeHeight - $status->nodeStatus->tron->explorerHeight) > self::TRON_BLOCK_FAULT) {
                throw new Exception('TRON discrepancy block height:' . $status->nodeStatus->tron->nodeHeight -  $status->nodeStatus->tron->explorerHeight . ' height');
            }

            HeartbeatStatusJob::dispatch(
                service: $this->service,
                status: HeartbeatStatus::Up,
                message: 'Processing status success',
                serviceLogLaunch: $serviceLogLaunch,
            );
        } catch (Throwable $e) {
            HeartbeatStatusJob::dispatch(
                service: $this->service,
                status: HeartbeatStatus::Down,
                message: 'Service is down. Reason: :reasonText.',
                messageVariable: ['reasonText' => $e->getMessage()],
                serviceLogLaunch: $serviceLogLaunch,
            );
            throw $e;
        }
    }

	/**
	 * @return void
	 */
    protected function initMonitor():void
    {
        $this->service = Service::where('slug', HeartbeatServiceName::CronProcessingStatusCheck)
            ->first();

        $this->serviceLogLaunch = ServiceLogLaunch::create([
            'service_id' => $this->service->id,
            'status'     => HeartbeatStatus::InProgress
        ]);
    }
}
