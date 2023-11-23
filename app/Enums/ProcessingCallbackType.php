<?php

namespace App\Enums;

enum ProcessingCallbackType: string
{
    case Watch    = 'watch';
    case Transfer = 'transfer';
    case Expired  = 'expired';
    case Deposit   = 'deposit';
    case TransferJob = 'transfer_job';
}
