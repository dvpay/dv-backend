<?php
declare(strict_types=1);

namespace App\Enums;

enum TelegramNotificationStatus: string
{
    case Disabled = 'disabled';
    case Enabled = 'enabled';
}