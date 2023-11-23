<?php

namespace App\Http\Resources\Heartbeat;

use App\Http\Resources\BaseResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ServiceLogLaunchResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'serviceName' => __($this->service->name),
            'serviceId'   => $this->service_id,
            'status'      => $this->status,
            'statusTitle' => __($this->statusTitle),
            'log'         => ServiceLogHistoryCollection::make($this->serviceLogs),
            'startAt'     => $this->start_at,
            'endedAt'     => $this->ended_at,

        ];
    }
}
