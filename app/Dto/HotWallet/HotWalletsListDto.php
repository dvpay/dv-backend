<?php

namespace App\Dto\HotWallet;

use App\Dto\ArrayDto;
use App\Models\User;

class HotWalletsListDto extends ArrayDto
{
    public readonly ?string $page;
    public readonly ?int $perPage;
    public readonly ?string $sortField;
    public readonly ?string $sortDirection;
    public readonly ?string $filterField;
    public readonly ?string $filterValue;
    public readonly User $user;
    public readonly bool $hideEmpty;
}
