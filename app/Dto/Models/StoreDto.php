<?php

declare(strict_types=1);

namespace App\Dto\Models;

use App\Dto\ArrayDto;

class StoreDto extends ArrayDto
{
    public readonly int $userId;
    public readonly string $name;
    public readonly string $site;
    public readonly string $currencyId;
    public readonly string $rateSource;
    public readonly string $rateScale;
    public readonly string $invoiceExpirationTime;
    public readonly string $returnUrl;
    public readonly string $successUrl;
    public readonly int $addressHoldTime;

    public readonly bool $status;
    public readonly bool $staticAddresses;
}
