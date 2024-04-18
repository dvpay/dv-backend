<?php

namespace App\Enums;

enum TransferType: string
{
    case Delegate = 'delegate';
    case Regular = 'regular';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
