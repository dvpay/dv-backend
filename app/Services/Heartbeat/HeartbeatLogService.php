<?php

declare(strict_types=1);

namespace App\Services\Heartbeat;

use App\Enums\HeartbeatStatus;
use App\Models\ServiceLog;
use Throwable;

class HeartbeatLogService
{
    private string $message;
    private array $variables = [];
    private string|null $launchID;

    public function setMessage(string $message = 'Service is ok!', array $variables = []): static
    {
        $this->message = $message;
        $this->variables = $variables;

        return $this;
    }

    public function setLaunchId(string|null $launchID = null): static
    {
        $this->launchID = $launchID;
        return $this;
    }

    /**
     * @throws Throwable
     */
    public function saveLog(): void
    {
        $log = new ServiceLog([
            'message' => $this->message,
            'message_variables' => $this->variables,
            'service_log_launch_id' => $this->launchID
        ]);

        $log->saveOrFail();
    }
}