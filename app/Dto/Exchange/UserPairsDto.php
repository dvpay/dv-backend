<?php

namespace App\Dto\Exchange;

use App\Dto\ArrayDto;
use App\Models\User;

class UserPairsDto extends ArrayDto
{
    public readonly int $exchangeId;
    public readonly int $userId;
    public readonly string $currencyFrom;
    public readonly string $currencyTo;
    public readonly string $symbol;
    public readonly string $type;
}