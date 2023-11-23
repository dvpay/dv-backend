<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\HeartbeatServiceName;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends  Model
{
    protected $fillable = [
        'name',
        'slug',
        'url',
    ];

    protected $casts = [
        'slug' => HeartbeatServiceName::class,
    ];

    public function serviceLogLaunch(): HasMany
    {
        return $this->hasMany(ServiceLogLaunch::class, 'service_id', 'id');
    }

    public function serviceLogLaunchLatest()
    {
        return $this->hasOne(ServiceLogLaunch::class, 'service_id', 'id')->latestOfMany();
    }
}