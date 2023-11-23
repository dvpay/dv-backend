<?php

namespace App\Http\Resources\Heartbeat;

use App\Enums\HeartbeatStatus;
use App\Helpers\DateTimeFormatter;
use App\Http\Resources\BaseResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class HeartbeatServiceResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        $status = Cache::get($this->slug->value);
        $messageVariable = $status['messageVariables'] ?? [];

        if (!$status) {
            $status = [
                'status' => HeartbeatStatus::Unknown->value,
                'message' => 'Service status is ' . HeartbeatStatus::Unknown->value . '.',
                'lastTrigger' => now()->format(DATE_ATOM),
            ];
        }
        return [
            'name'        => __($this->name),
            'serviceId'   => $this->id,
            'slug'        => $this->slug,
            'lastLaunch'  => ServiceLogLaunchResource::make($this->serviceLogLaunchLatest),
            'status'      => $status['status'],
            'statusTitle' => __($status['status']),
            'message'     => __($status['message'], $messageVariable),
            'lastTrigger' => $status['lastTrigger'],
            'ago'         => DateTimeFormatter::getAgoTimeText($status['lastTrigger'])
        ];
    }
}
