<?php

namespace App\Http\Resources\Setting;

use App\Http\Resources\BaseResource;
use Illuminate\Http\Request;

class SettingResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'value' => $this->value,
        ];
    }
}
