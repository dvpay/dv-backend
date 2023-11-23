<?php

namespace App\Models\Notification;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NotificationTarget extends Model
{
    use SoftDeletes;

    protected $fillable = [
            'slug'
    ];
}
