<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\Blockchain;
use App\Enums\HeartbeatServiceName;
use App\Enums\HeartbeatStatus;
use App\Jobs\HeartbeatStatusJob;
use App\Models\Service;
use App\Models\ServiceLogLaunch;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 *
 */
class NodeStatusCheck extends Command
{

    private const TIME_BLOCK = 10;

    /**
     * @var ServiceLogLaunch
     */
    private ServiceLogLaunch $serviceLogLaunch;
    /**
     * @var Service
     */
    private Service $service;

    /**
     * @param Client $client
     * @param string $nodeUrl
     */
    public function __construct(
        private readonly Client $client,
        private readonly string $nodeUrl
    )
    {
        parent::__construct();
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'node:status:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check node status.';

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
            message: 'Start Check Node status',
            serviceLogLaunch: $this->serviceLogLaunch,
        );


        try {
            $time = time();

            $blockchains = Blockchain::cases();
            foreach ($blockchains as $blockchain) {
                if ($blockchain == Blockchain::Tron) {
                    $this->checkTronNodeStatus();
                }

                if ($blockchain == Blockchain::Bitcoin) {
                    $this->checkBitcoinNodeStatus();
                }
            }

            HeartbeatStatusJob::dispatch(
                service: $this->service,
                status: HeartbeatStatus::Up,
                message: 'Node status success status',
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
     */
    private function checkTronNodeStatus(): void
    {
        $service = Service::where('slug', HeartbeatServiceName::NodeTron)
            ->first();

        $serviceLogLaunch = ServiceLogLaunch::create([
            'service_id' => $service->id,
            'status'     => HeartbeatStatus::InProgress
        ]);

        try {
            $status = $this->client->get($this->nodeUrl . '/status/tron');
            if ($status->getStatusCode() != Response::HTTP_OK) {
                throw new Exception('Tron node response with status code: ' . $status->getStatusCode());
            }

            $status = json_decode($status->getBody()->getContents());
            if (!$status->success) {
                throw new Exception('Tron node response with status false');
            }

            if (time() - $status->lastBlockAt > self::TIME_BLOCK * 60) {
                throw new Exception('Tron node last block generated more ' . self::TIME_BLOCK . ' minutes ago');
            }

            HeartbeatStatusJob::dispatch(
                service: $service,
                status: HeartbeatStatus::Up,
                message: 'Tron Node status success',
                serviceLogLaunch: $serviceLogLaunch,
            );
        } catch (Throwable $e) {
            HeartbeatStatusJob::dispatch(
                service: $service,
                status: HeartbeatStatus::Down,
                message: 'Service is down. Reason: :reasonText.',
                messageVariable: ['reasonText' => $e->getMessage()],
                serviceLogLaunch: $serviceLogLaunch,
            );
        }
    }

    /**
     * @return void
     */
    private function checkBitcoinNodeStatus(): void
    {
        $service = Service::where('slug', HeartbeatServiceName::NodeTron)
            ->first();

        $serviceLogLaunch = ServiceLogLaunch::create([
            'service_id' => $service->id,
            'status'     => HeartbeatStatus::InProgress
        ]);

        try {
            $status = $this->client->get($this->nodeUrl . '/status/bitcoin');
            if ($status->getStatusCode() != Response::HTTP_OK) {
                throw new Exception('Bitcoin node response with status code: ' . $status->getStatusCode());
            }

            $status = json_decode($status->getBody()->getContents());
            if (!$status->success) {
                throw new Exception('Bitcoin node response with status false');
            }

            if (time() - $status->lastBlockAt > self::TIME_BLOCK * 60) {
                throw new Exception('Bitcoin node last block generated more ' . self::TIME_BLOCK . ' minutes ago');
            }

            HeartbeatStatusJob::dispatch(
                service: $service,
                status: HeartbeatStatus::Up,
                message: 'Tron Node status success',
                serviceLogLaunch: $serviceLogLaunch,
            );
        } catch (Throwable $e) {
            HeartbeatStatusJob::dispatch(
                service: $service,
                status: HeartbeatStatus::Down,
                message: 'Service is down. Reason: :reasonText.',
                messageVariable: ['reasonText' => $e->getMessage()],
                serviceLogLaunch: $serviceLogLaunch,
            );
        }
    }

    protected function initMonitor(): void
    {
        $this->service = Service::where('slug', HeartbeatServiceName::CronNodeVersionStatus)
            ->first();

        $this->serviceLogLaunch = ServiceLogLaunch::create([
            'service_id' => $this->service->id,
            'status'     => HeartbeatStatus::InProgress
        ]);
    }
}