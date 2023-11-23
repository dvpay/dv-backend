<?php

namespace App\Dto\Exchange;

use App\Dto\ArrayDto;
use App\Enums\ExchangeAddressType;

class DepositAddressDto extends ArrayDto
{
    public readonly string $currency;
    public readonly ?string $exchangeUserId;
    public readonly string $address;
    public readonly string $chain;
    public readonly ExchangeAddressType $type;

}