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
 * NodeVersionControl
 */
class NodeVersionControl extends Command
{
    /**
     * @param Client $client
     * @param string $nodeUrl
     */

    /**
     * @var ServiceLogLaunch
     */
    private ServiceLogLaunch $serviceLogLaunch;
    /**
     * @var Service
     */
    private Service $service;

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
    protected $signature = 'node:version:control';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check node version.';

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
            message: 'Start Check Node version',
            serviceLogLaunch: $this->serviceLogLaunch,
        );

        try {
            $time = time();

            $blockchains = Blockchain::cases();
            foreach ($blockchains as $blockchain) {
                if ($blockchain == Blockchain::Tron) {
                    $this->checkTronVersion();
                }

                if ($blockchain == Blockchain::Bitcoin) {
                    $this->checkBitcoinVersion();
                }
            }

            HeartbeatStatusJob::dispatch(
                service: $this->service,
                status: HeartbeatStatus::Up,
                message: 'Node version success',
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
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function checkTronVersion(): void
    {
        $service = Service::where('slug', HeartbeatServiceName::NodeTronVersion)
            ->first();

        $serviceLogLaunch = ServiceLogLaunch::create([
            'service_id' => $service->id,
            'status'     => HeartbeatStatus::InProgress
        ]);

        try {
            $actualVersion = $this->client->get('https://api.github.com/repos/tronprotocol/java-tron/releases/latest');
            if ($actualVersion->getStatusCode() != Response::HTTP_OK) {
                throw new Exception('Tron version control API response with status code: ' . $actualVersion->getStatusCode());
            }

            $ourVersion = $this->client->get($this->nodeUrl . '/status/tron');
            if ($ourVersion->getStatusCode() != Response::HTTP_OK) {
                throw new Exception('Tron version control API response with status code: ' . $ourVersion->getStatusCode());
            }

            $actualVersion = json_decode($actualVersion->getBody()->getContents());
            $actualVersion = explode('-', $actualVersion->tag_name);
            $actualVersion = str_replace('v', '', $actualVersion[1]);

            $ourVersion = json_decode($ourVersion->getBody()->getContents());
            $ourVersion = $ourVersion->version;

            if ($ourVersion != $actualVersion) {
                HeartbeatStatusJob::dispatch(
                    service: $service,
                    status: HeartbeatStatus::Down,
                    message: 'Service version is outdated! Current version is :currentVersion. Last version is :lastVersion.',
                    messageVariable: [
                        'currentVersion' => $ourVersion,
                        'lastVersion'    => $actualVersion,
                    ],
                    serviceLogLaunch: $serviceLogLaunch,
                );

                return;
            }

            HeartbeatStatusJob::dispatch(
                service: $service,
                status: HeartbeatStatus::Up,
                message: 'Tron Node version success',
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
            throw $e;
        }
    }

    /**
     * @return void
     * @throws Throwable
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function checkBitcoinVersion(): void
    {
        $service = Service::where('slug', HeartbeatServiceName::NodeTronVersion)
            ->first();

        $serviceLogLaunch = ServiceLogLaunch::create([
            'service_id' => $service->id,
            'status'     => HeartbeatStatus::InProgress
        ]);

        try {
            $actualVersion = $this->client->get('https://api.github.com/repos/bitcoin/bitcoin/releases/latest');
            if ($actualVersion->getStatusCode() != Response::HTTP_OK) {
                throw new Exception('Bitcoin version control API response with status code: ' . $actualVersion->getStatusCode());
            }

            $ourVersion = $this->client->get($this->nodeUrl . '/status/bitcoin');
            if ($ourVersion->getStatusCode() != Response::HTTP_OK) {
                throw new Exception('Bitcoin version control API response with status code: ' . $ourVersion->getStatusCode());
            }

            $actualVersion = json_decode($actualVersion->getBody()->getContents());
            $actual = str_replace('v', '', $actualVersion->tag_name);

            $ourVersion = json_decode($ourVersion->getBody()->getContents());
            $our = $ourVersion->version;
            $ourVersionParts = str_split($our, 2);

            $ourVersionFormatted = (int)$ourVersionParts[0] . '.' . (int)$ourVersionParts[1] . '.' . (int)$ourVersionParts[2];

            if ($actual != $ourVersionFormatted) {
                HeartbeatStatusJob::dispatch(
                    service: $service,
                    status: HeartbeatStatus::Down,
                    message: 'Service version is outdated! Current version is :currentVersion. Last version is :lastVersion.',
                    messageVariable: [
                        'currentVersion' => $ourVersionFormatted,
                        'lastVersion'    => $actual,
                    ],
                    serviceLogLaunch: $serviceLogLaunch,
                );

                return;
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

            throw $e;
        }
    }
    protected function initMonitor():void
    {
        $this->service = Service::where('slug', HeartbeatServiceName::CronNodeVersionControl)
            ->first();

        $this->serviceLogLaunch = ServiceLogLaunch::create([
            'service_id' => $this->service->id,
            'status'     => HeartbeatStatus::InProgress
        ]);
    }
}