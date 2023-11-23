<?php

namespace App\Enums;

enum ExchangeAddressType: string
{
    case Deposit = 'Deposit';
    case Withdraw = 'Withdraw';
}