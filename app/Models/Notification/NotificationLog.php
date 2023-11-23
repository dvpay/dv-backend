<?php

namespace App\Models\Notification;

use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
    protected $fillable = [
        'email',
        'text',
        'model_type',
        'model_id',
        'model_variable',
    ];

    protected $casts = [
        'model_variable' => 'json'
    ];
}
