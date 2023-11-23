<?php

namespace App\Http\Resources\Heartbeat;

use App\Http\Resources\BaseCollection;
use Illuminate\Http\Request;

/** @see \App\Models\ServiceLogLaunch */
class ServiceLogLaunchCollection extends BaseCollection
{
    public $collects = ServiceLogLaunchResource::class;
}
