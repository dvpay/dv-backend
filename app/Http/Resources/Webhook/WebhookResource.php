<?php

declare(strict_types=1);

namespace App\Http\Resources\Webhook;

use App\Http\Resources\BaseResource;

class WebhookResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'url' => $this->url,
            'secret' => $this->secret,
            'enabled' => $this->enabled,
            'events' => $this->events,
        ];
    }
}