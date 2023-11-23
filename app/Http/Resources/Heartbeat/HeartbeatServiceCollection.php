<?php

namespace App\Http\Resources\Heartbeat;

use App\Http\Resources\BaseCollection;

/** @see \App\Models\Service */
class HeartbeatServiceCollection extends BaseCollection
{
    public $collects = HeartbeatServiceResource::class;
}
