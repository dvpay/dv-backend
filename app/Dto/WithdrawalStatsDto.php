<?php

declare(strict_types=1);

namespace App\Dto;

class WithdrawalStatsDto extends ArrayDto
{
    public readonly string $page;
    public readonly int $perPage;
    public readonly string $sortField;
    public readonly string $sortDirection;
}
