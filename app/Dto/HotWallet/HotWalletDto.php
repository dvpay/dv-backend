<?php

namespace App\Dto\HotWallet;

use App\Dto\ArrayDto;
use App\Enums\Blockchain;
use App\Enums\CurrencyId;
use App\Enums\HotWalletState;
use App\Models\User;

class HotWalletDto extends ArrayDto
{
    public readonly CurrencyId $currencyId;
    public readonly User $user;
    public readonly string $address;
    public readonly Blockchain $blockchain;
    public readonly HotWalletState $state;
    public ?float $amount = 0;
    public ?float $amountUsd = 0;
}
