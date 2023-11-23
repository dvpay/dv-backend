<?php

declare(strict_types=1);

namespace App\Dto\Models;

use App\Dto\ArrayDto;
use App\Enums\Blockchain;
use App\Models\Store;

class WalletDto extends ArrayDto
{
    public readonly string $address;
    public readonly Blockchain $blockchain;
    public readonly Store $store;
    public readonly bool $readonly;
    public readonly string $mnemonic;
    public readonly string $passPhrase;
}