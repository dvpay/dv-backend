<?php

declare(strict_types=1);

namespace App\Dto;

use App\Models\User;

class CreateWithdrawalDto extends ArrayDto
{
    public readonly string $currencyId;
    public readonly bool $isManual;
    public readonly User $user;
}