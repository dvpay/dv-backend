<?php

namespace App\Http\Controllers\Api\Root;

use App\Enums\RootSetting;
use App\Facades\Settings;
use App\Http\Controllers\Controller;
use App\Http\Requests\Root\SettingUpdateRequest;
use App\Http\Resources\Setting\SettingCollection;
use App\Http\Resources\Setting\SettingResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SettingController extends Controller
{
    /**
     * @return SettingCollection
     */
    public function index(): SettingCollection
    {
        return SettingCollection::make(Settings::allWithDefaults());
    }

    /**
     * @param SettingUpdateRequest $request
     * @return SettingCollection
     */
    public function update(SettingUpdateRequest $request): SettingCollection
    {
        Settings::set($request->input('key'), $request->input('value'));
        return SettingCollection::make(Settings::all());
    }

    public function registration()
    {
        $response = (object)[
            'name' => RootSetting::RegistrationEnable->value,
            'value' => Settings::get(RootSetting::RegistrationEnable->value)
        ];

        return SettingResource::make($response );
    }
}
