<?php

declare(strict_types=1);

namespace App\Models;


use App\Enums\HeartbeatStatus;

class ServiceLog extends Model
{
    protected $fillable = [
        'message',
        'message_variables',
        'service_log_launch_id',
        'memory'
    ];

    protected $casts = [
        'status'            => HeartbeatStatus::class,
        'message_variables' => 'json',
    ];
}