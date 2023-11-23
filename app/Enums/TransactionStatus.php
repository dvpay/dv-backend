<?php

declare(strict_types=1);

namespace App\Enums;

enum TransactionStatus: string
{
    case Processed = 'processed';
    case Waiting = 'waiting';
}
