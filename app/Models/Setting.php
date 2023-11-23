<?php

namespace App\Models;

use App\Models\Settings\AbstractSetting;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Setting extends AbstractSetting
{

    public static function getSettingsDefinitions (): array
    {
        return [
            /**
             * Global settings for service,
             * these are not assigned to any model.
             */
            [
                'name'    => 'registration_enable',
                'cast'    => 'boolean',
                'default' => true
            ],
        ];
    }
}
