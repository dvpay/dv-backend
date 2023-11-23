<?php

declare(strict_types=1);

namespace App\Services\Heartbeat;

use App\Dto\ServiceLogHistoryDto;
use App\Enums\HeartbeatServiceName;
use App\Enums\HeartbeatStatus;
use App\Jobs\HeartbeatStatusJob;
use App\Models\Model;
use App\Models\Service;
use App\Models\ServiceLog;
use App\Models\ServiceLogLaunch;
use DateTime;
use Illuminate\Cache\Repository;
use Illuminate\Contracts\Queue\Factory;
use Illuminate\Support\Collection;
use LaravelIdea\Helper\App\Models\_IH_Model_C;
use LaravelIdea\Helper\App\Models\_IH_Service_C;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * HeartbeatService
 */
class HeartbeatService
{
    /**
     * @param Repository $cache
     */
    public function __construct(
        private readonly Repository $cache,
        protected readonly Factory  $manager
    )
    {
    }

    /**
     * @param HeartbeatServiceName $serviceName
     * @param HeartbeatStatus $status
     * @param string $message
     * @param array $messageVariables
     * @return void
     * @throws InvalidArgumentException
     */
    public function setStatus(
        HeartbeatServiceName $serviceName,
        HeartbeatStatus      $status,
        string               $message,
        array                $messageVariables = []
    ): void
    {
        $data = [
            'name' => $serviceName->title(),
            'status' => $status->value,
            'message' => $message,
            'messageVariables' => $messageVariables,
            'lastTrigger' => (new DateTime())->format(DATE_ATOM),
        ];

        $this->cache->set($serviceName->value, $data, 86400);
    }

    /**
     * @return array
     * @throws InvalidArgumentException
     */
    public function getSystemStatus(): array
    {
        $status = $this->cache->get(HeartbeatServiceName::System->value);

        if (!$status) {
            $name = HeartbeatServiceName::System;
            $status = HeartbeatStatus::Up;
            $message = 'System is ok!';

            $this->setStatus($name, $status, $message);

            $status = [
                'name' => $name->title(),
                'status' => $status,
                'message' => $message,
                'lastTrigger' => (new DateTime())->format(DATE_ATOM),
            ];
        }

        return $status;
    }

    /**
     * @return void
     * @throws InvalidArgumentException
     */
    public function updateSystemStatus(Service $serviceHeartbeat, ServiceLogLaunch $serviceLogLaunch): void
    {
        $services = Service::all();
        foreach ($services as $service) {
            $status = $this->cache->get($service->slug->value);

            if (!$status) {
                continue;
            }

            if ($status['status'] == HeartbeatStatus::Down) {
                $this->setStatus(
                    HeartbeatServiceName::System,
                    HeartbeatStatus::Down,
                    $status['message'],
                    $status['messageVariables']
                );

                HeartbeatStatusJob::dispatch(
                    service: $serviceHeartbeat,
                    status: HeartbeatStatus::Down,
                    message: $status['message'],
                    messageVariable: $status['messageVariables'],
                    serviceLogLaunch: $serviceLogLaunch,
                );

                break;
            }

            $this->setStatus(
                HeartbeatServiceName::System,
                HeartbeatStatus::Up,
                'System is ok!'
            );

            HeartbeatStatusJob::dispatch(
                service: $serviceHeartbeat,
                status: HeartbeatStatus::Up,
                message: 'System is ok!',
                serviceLogLaunch: $serviceLogLaunch,
            );

        }
    }

    /**
     * @return array
     */
    public function getStatusForDashboard(): array
    {
        $statuses = [];

        $services = HeartbeatServiceName::forDashboard();
        foreach ($services as $service) {
            $status = $this->cache->get($service->value);

            if (!$status) {
                $status = [
                    'name' => $service->title(),
                    'status' => HeartbeatStatus::Unknown->value,
                    'message' => 'Service status is ' . HeartbeatStatus::Unknown->value . '.',
                    'messageVariables' => [],
                    'lastTrigger' => (new DateTime())->format(DATE_ATOM),
                ];
            }

            $messageVariable = $status['messageVariables'] ?? [];

            $statuses[] = [
                'name' => __($status['name']),
                'status' => $status['status'],
                'statusTitle' => __($status['status']),
                'message' => __($status['message'], $messageVariable),
                'lastTrigger' => $status['lastTrigger'],
                'ago' => $this->getAgoTimeText($status['lastTrigger']),
            ];
        }

        return $statuses;
    }

    /**
     * @param string $datetime
     *
     * @return string
     * @throws \Exception
     */
    private function getAgoTimeText(string $datetime): string
    {
        $now = new DateTime;
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);

        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;

        $string = [
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        ];

        foreach ($string as $key => &$value) {
            if ($diff->$key) {
                $value = $diff->$key . ' ' . $value . ($diff->$key > 1 ? 's' : '');
            } else {
                unset($string[$key]);
            }
        }

        return $string ? implode(', ', $string) . ' ' . __('ago') : __('just now');
    }

    /**
     * @param ServiceLogHistoryDto $dto
     *
     * @return mixed
     */
    public function getServiceLogHistory(ServiceLogHistoryDto $dto)
    {
        $query = ServiceLog::where('service_id', $dto->serviceId);

        if (!empty($dto->status) && in_array($dto->status,
                [HeartbeatStatus::Down->value, HeartbeatStatus::Up->value])) {
            $query->whereStatus($dto->status);
        }

        return $query->orderBy($dto->sortField, $dto->sortDirection)
            ->paginate($dto->perPage);
    }

    public function getQueues(): Collection
    {
        return collect(config('queue.app_queue'))->map(function ($queue) {
            $connection = config('queue.default');
            return [
                'connection' => $connection,
                'queue' => $queue,
                'size' => $this->manager->connection($connection)->size($queue),
            ];
        });
    }

    public function getDiskSpace(): object
    {
        $diskTotal = disk_total_space('/'); //DISK usage
        $diskFree = disk_free_space('/');
        $diskSpaceFree = round(100 - ((($diskTotal - $diskFree) / $diskTotal) * 100));

        return (object)[
            'diskTotal' => $this->formatBytes($diskTotal),
            'diskFree' => $this->formatBytes($diskFree),
            'diskSpaceFreePercent' => $diskSpaceFree,
        ];
    }

    private function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * @return Model[]|Service[]|_IH_Model_C|_IH_Service_C
     */
    public function getAllService()
    {
        return Service::with('serviceLogLaunchLatest')
            ->get();
    }
}