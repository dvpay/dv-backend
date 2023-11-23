<?php

namespace App\Http\Resources\Setting;

use App\Http\Resources\BaseCollection;
use Illuminate\Http\Request;

/** @see \App\Models\Setting */
class SettingCollection extends BaseCollection
{
    public $collects = SettingResource::class;
}
