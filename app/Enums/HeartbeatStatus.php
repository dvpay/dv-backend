<?php

declare(strict_types=1);

namespace App\Enums;

enum HeartbeatStatus: string
{
    case Up = 'up';
    case Down = 'down';
    case Unknown = 'unknown';
    case InProgress = 'in progress';
}