<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\EnumToArray;
use ReflectionClass;

enum WithdrawalInterval: string
{
    use EnumToArray;

    case Never = '0 2 31 2 1'; //  Feb 31st hack for cron not started
    case EveryOneMin = '* * * * *';
    case Every12hours = '0 */12 * * *';
    case EveryDay = '0 0 * * *';
    case Every3Days = '0 1 */3 * *';
    case EveryWeek = '0 2 * * 1';

    public function getName(): string
    {
        return match ($this) {
            self::Never => __('Never'),
            self::EveryOneMin => __('Every 1 minutes'),
            self::Every12hours => __('Every 12 hours'),
            self::EveryDay => __('Every Day'),
            self::Every3Days => __('Every 3 Day'),
            self::EveryWeek => __('Every week'),
        };
    }

    public static function getValue(): array
    {
        $reflection = new ReflectionClass(self::class);
        $constants = $reflection->getConstants();
        $keyValuePairs = [];

        foreach ($constants as $key => $value) {
            $keyValuePairs[$key] = $value;
        }

        return $keyValuePairs;
    }
}
