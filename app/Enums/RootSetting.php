<?php

declare(strict_types=1);

namespace App\Enums;

enum RootSetting: string
{
    case RegistrationEnable = 'registration_enable';
    case AppUrl = 'app_url';


    public function cast(): string
    {
        return match ($this)
        {
            RootSetting::RegistrationEnable => 'boolean',
            default => 'string'
        };
    }

    public function default(): mixed
    {
        return match ($this)
        {
            RootSetting::RegistrationEnable => true,
            RootSetting::AppUrl => config('app.url'),
            default => null
        };
    }

    public static function getRootSettingsDefinitions(): array
    {
        return array_reduce(self::cases(), function ($carry, $setting) {
            $carry[] = [
                'name'    => $setting->value,
                'cast'    => $setting->cast(),
                'default' => $setting->default()
            ];
            return $carry;
        },[]);
    }
}