<?php

namespace App\Console\Commands;

use App\Notifications\DiskFreeSpaceNotification;
use App\Services\Heartbeat\HeartbeatService;
use App\Services\User\UserService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class DiskFreeCheckCommand extends Command
{
    protected $signature = 'disk:free:check';

    protected $description = 'Command description';

    protected const MEMORY_PERCENT_LIMIT = 10;

    public function __construct(
            private readonly HeartbeatService $heartbeatService,
            private readonly UserService $userService,
    )
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $memory = $this->heartbeatService->getDiskSpace();

        $this->newLine();

        $this->components->twoColumnDetail('<fg=gray>Memory Info</>', '<fg=gray>Free / Total</>');
        $this->components->twoColumnDetail('Disk', $memory->diskFree . '/' . $memory->diskTotal);

        if($memory->diskSpaceFreePercent <= self::MEMORY_PERCENT_LIMIT) {
            Notification::send($this->userService->getAllRoot(), new DiskFreeSpaceNotification);

        };
    }
}
