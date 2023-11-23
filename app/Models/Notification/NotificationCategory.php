<?php

namespace App\Models\Notification;


use App\Models\Model;

class NotificationCategory extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function notification()
    {
        return $this->hasMany(Notification::class);
    }
}