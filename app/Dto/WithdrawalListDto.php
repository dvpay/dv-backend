<?php

declare(strict_types=1);

namespace App\Dto;

use App\Models\User;

class WithdrawalListDto extends ArrayDto
{
    public readonly string $page;
    public readonly int $perPage;
    public readonly string $sortField;
    public readonly string $sortDirection;
    public readonly User $user;
    public readonly string $dateFrom;
    public readonly string $dateTo;
}
