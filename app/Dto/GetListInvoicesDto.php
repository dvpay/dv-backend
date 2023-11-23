<?php

declare(strict_types=1);

namespace App\Dto;

use App\Models\User;

class GetListInvoicesDto extends ArrayDto
{
    public readonly string $query;
    public readonly string $page;
    public readonly int $perPage;
    public readonly string $sortField;
    public readonly string $sortDirection;
    public readonly User $user;
    public readonly array $stores;
}