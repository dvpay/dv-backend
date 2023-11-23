<?php

namespace App\Enums;

enum HotWalletState: string
{
    case Free = 'free';
    case Busy = 'busy';
    case Hold = 'hold';

    public function title(): string
    {
        return match ($this)
        {
            self::Free => 'Ready to use.',
            self::Busy => 'Linked to invoice.',
            self::Hold => 'Hold.',
        };
    }
}
