<?php

namespace App\Enums;

enum PermissionsEnum: string
{
    case ExchangeStop = 'stop_exchange';
    case TransfersFunds = 'transfer funds';
    case StopStorePay = 'stop pay';
}