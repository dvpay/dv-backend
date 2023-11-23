<?php

declare(strict_types=1);

namespace App\Enums;

enum Locale: string
{
    case EN = 'en';
    case RU = 'ru';
    case ES = 'es';
    case HI = 'hi';

    public static function asArray(): array
    {
        $locales = Locale::cases();

        $result = [];
        foreach ($locales as $locale) {
            $result[] = $locale->value;
        }

        return $result;
    }
}
