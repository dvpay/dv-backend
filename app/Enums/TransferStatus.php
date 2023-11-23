<?php

namespace App\Enums;

enum TransferStatus: string
{
    case Waiting = 'waiting';
    case Sending = 'sending';
    case Complete = 'complete';
    case Failed = 'failed';
}
