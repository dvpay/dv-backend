<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\HeartbeatServiceName;
use App\Enums\HeartbeatStatus;
use App\Enums\HttpMethod;
use App\Jobs\HeartbeatStatusJob;
use App\Models\Service;
use App\Models\ServiceLogLaunch;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * ExplorerServiceStatusCheck
 */
class ExplorerServiceStatusCheck extends Command
{
    private const TIME_BLOCK = 10;

    private ServiceLogLaunch $serviceLogLaunch;
    private Service $service;

    /**
     * @param Client $client
     * @param string $bitcoinExplorerUrl
     * @param string $tronExplorerUrl
     */
    public function __construct(
        private readonly Client $client,
        private readonly string $bitcoinExplorerUrl,
        private readonly string $tronExplorerUrl
    )
    {
        parent::__construct();
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'explorer:status:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check explorer services status.';

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
            message: 'Start check status Explorer',
            serviceLogLaunch: $this->serviceLogLaunch,
        );

        try {
            $time = time();

            $this->checkBitcoinExplorerStatus();
            $this->checkTronExplorerStatus();

            HeartbeatStatusJob::dispatch(
                service: $this->service,
                status: HeartbeatStatus::Up,
                message: 'Explorer cron status ok',
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
    public function checkBitcoinExplorerStatus(): void
    {
        $service = Service::where('slug', HeartbeatServiceName::ServiceBitcoinExplorer)
            ->first();

        $serviceLogLaunch = ServiceLogLaunch::create([
            'service_id' => $service->id,
            'status'     => HeartbeatStatus::InProgress
        ]);

        try {
            $response = $this->client->request(HttpMethod::GET->value, $this->bitcoinExplorerUrl . '/status', []);

            if ($response->getStatusCode() != Response::HTTP_OK) {
                throw new Exception('Bitcoin Explorer response with status code: ' . $response->getStatusCode());
            }

            $response = json_decode($response->getBody()->getContents());
            if (!$response->success) {
                throw new Exception('Bitcoin Explorer response with status false');
            }

            if (time() - $response->lastBlockAt > self::TIME_BLOCK * 60) {
                throw new Exception('Bitcoin Explorer last block generated more ' . self::TIME_BLOCK . ' minutes ago');
            }

            HeartbeatStatusJob::dispatch(
                service: $service,
                status: HeartbeatStatus::Up,
                message: 'Bitcoin Explorer Node status success',
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
    public function checkTronExplorerStatus(): void
    {
        $service = Service::where('slug', HeartbeatServiceName::ServiceBitcoinExplorer)
            ->first();

        $serviceLogLaunch = ServiceLogLaunch::create([
            'service_id' => $service->id,
            'status'     => HeartbeatStatus::InProgress
        ]);

        try {
            $response = $this->client->request(HttpMethod::GET->value, $this->tronExplorerUrl . '/status', []);

            if ($response->getStatusCode() != Response::HTTP_OK) {
                throw new Exception('Tron Explorer response with status code: ' . $response->getStatusCode());
            }

            $response = json_decode($response->getBody()->getContents());
            if (!$response->success) {
                throw new Exception('Tron Explorer response with status false');
            }

            if (time() - $response->lastBlockAt > self::TIME_BLOCK * 60) {
                throw new Exception('Tron Explorer last block generated more ' . self::TIME_BLOCK . ' minutes ago');
            }

            HeartbeatStatusJob::dispatch(
                service: $service,
                status: HeartbeatStatus::Up,
                message: 'Tron Explorer Node status success',
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
        $this->service = Service::where('slug', HeartbeatServiceName::CronExplorerStatusCheck)
            ->first();

        $this->serviceLogLaunch = ServiceLogLaunch::create([
            'service_id' => $this->service->id,
            'status'     => HeartbeatStatus::InProgress
        ]);

    }
}
