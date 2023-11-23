<?php

declare(strict_types=1);

namespace App\Http\Resources\ApiKey;

use App\Http\Resources\BaseResource;

class ApiKeyResource extends BaseResource
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
            'storeId' => $this->store_id,
            'key' => $this->key,
            'enabled' => $this->enabled,
        ];
    }
}