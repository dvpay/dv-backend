<?php

declare(strict_types=1);

namespace App\Dto;

use App\Models\User;

class ExchangeKeyAddDto extends ArrayDto
{
    public readonly string $exchange;
    public readonly array $keys;
    public readonly User $user;

}