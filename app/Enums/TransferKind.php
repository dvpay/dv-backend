<?php

namespace App\Enums;

enum TransferKind: string
{
    case TransferFromAddress = 'transferFromAddress';
    case TransferFromProcessing = 'transferFromProcessing';

}
