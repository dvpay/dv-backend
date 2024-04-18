<?php
declare(strict_types=1);

namespace App\Services\Processing\Contracts;

use App\Models\Currency;
use App\Models\Payer;

interface AddressContract
{
    public function getAll(string $ownerId): array;

    public function getStaticAddress(Currency $currency, Payer $payer, string $ownerId): array;
}