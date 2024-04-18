<?php

namespace App\Models;

use App\Enums\RootSetting;
use App\Models\Settings\AbstractSetting;

class Setting extends AbstractSetting
{

    public static function getSettingsDefinitions(): array
    {
        /**
         * Global settings for service,
         * these are not assigned to any model.
         */
        return RootSetting::getRootSettingsDefinitions();
    }
}
