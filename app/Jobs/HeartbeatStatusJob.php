<?php

namespace App\Jobs;

use App\Enums\HeartbeatServiceName;
use App\Enums\HeartbeatStatus;
use App\Enums\Queue;
use App\Models\Service;
use App\Models\ServiceLogLaunch;
use App\Services\Heartbeat\HeartbeatLogService;
use App\Services\Heartbeat\HeartbeatService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Psr\SimpleCache\InvalidArgumentException;
use Throwable;

/**
 * TelegramNotificationJob
 */
class HeartbeatStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @param Service $service
     * @param HeartbeatStatus $status
     * @param string $message
     * @param array $messageVariable
     * @param ServiceLogLaunch|null $serviceLogLaunch
     */
    public function __construct(
        private readonly Service               $service,
        private readonly HeartbeatStatus       $status,
        private readonly string                $message = 'Service is ok!',
        private readonly array                 $messageVariable = [],
        private readonly null|ServiceLogLaunch $serviceLogLaunch = null
    )
    {
        $this->onQueue(Queue::Monitor->value);
    }

    /**
     * @param HeartbeatService $heartbeatService
     * @param HeartbeatLogService $heartbeatLogService
     * @return void
     * @throws Throwable
     * @throws InvalidArgumentException
     */
    public
    function handle(HeartbeatService $heartbeatService, HeartbeatLogService $heartbeatLogService): void
    {
        $heartbeatService->setStatus(
            $this->service->slug,
            $this->status,
            $this->message,
            $this->messageVariable
        );

        if ($this->status === HeartBeatStatus::Up || $this->status === HeartbeatStatus::Down) {
            $this->serviceLogLaunch->ended_at = now();
        }
        $this->serviceLogLaunch->status = $this->status;
        $this->serviceLogLaunch->save();

        $heartbeatLogService
            ->setMessage($this->message, $this->messageVariable)
            ->setLaunchId($this->serviceLogLaunch?->id)
            ->saveLog();
    }
}
