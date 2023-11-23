<?php

declare(strict_types=1);

namespace App\Dto;

use App\Enums\TimeRange;

class GetBalancesDto extends ArrayDto
{
    public readonly string $currencyId;
    public readonly TimeRange $range;
}