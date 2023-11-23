<?php

namespace App\Models;

use App\Enums\HeartbeatStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ServiceLogLaunch extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'service_id',
        'status',
        'start_at',
        'ended_at',
    ];

    protected $casts = [
        'status'   => HeartbeatStatus::class,
        'start_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->start_at = $model->freshTimestamp();
        });
    }

    public function serviceLogs(): HasMany
    {
        return $this->hasMany(ServiceLog::class, 'service_log_launch_id', 'id');
    }

    public function service(): HasOne
    {
        return $this->hasOne(Service::class, 'id', 'service_id');
    }
}
