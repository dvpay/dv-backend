<?php

namespace App\Http\Resources\Notification;

use App\Http\Resources\BaseResource;
use Illuminate\Http\Request;

class NotificationTargetResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
                'id'   => $this->id,
                'slug' => $this->slug
        ];
    }
}
