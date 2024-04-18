<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Setting;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * SettingUpdatedEvent
 */
class SettingUpdatedEvent
{
    use Dispatchable, InteractsWithQueue, Queueable, InteractsWithSockets, SerializesModels;

    /**
     * @param Setting $setting
     */
    public function __construct(public readonly Setting $setting)
    {
    }
}