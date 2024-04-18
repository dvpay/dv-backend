<?php

namespace Database\Seeders;

use App\Enums\RootSetting;
use App\Facades\Settings;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        if(!Settings::get(RootSetting::RegistrationEnable->value)) {
            Settings::set(RootSetting::RegistrationEnable->value, true);
        };
    }
}
