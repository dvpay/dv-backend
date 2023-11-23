<?php

namespace App\Http\Resources\User;

use App\Http\Resources\BaseResource;

class TokenResource extends BaseResource
{
    public function toArray($request)
    {
        return [
            'token' => $this->resource
        ];
    }
}