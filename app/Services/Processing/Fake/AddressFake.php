<?php

declare(strict_types=1);

namespace App\Services\Processing\Fake;

use App\Enums\Blockchain;
use App\Models\Currency;
use App\Models\Payer;
use App\Services\Processing\Contracts\AddressContract;

class AddressFake implements AddressContract
{
    public function getAll(string $ownerId): array
    {
        return [
            [
                "blockchain" => "tron",
                "address"    => fake()->lexify('TKn5GuNb62KgQh7SLXznUrP33Nae??????'),
                "balance"    => rand(0, 50000),
                "state"      => "free",
                "type"       => "sc",
            ],
            [
                "blockchain" => "tron",
                "address"    => fake()->lexify('TKn5GuNb62KgQh7SLXznUrP33Nae??????'),
                "balance"    => rand(0, 50000),
                "state"      => "busy",
                "type"       => "sc",
            ],
        ];
    }

    public function getStaticAddress(Currency $currency, Payer $payer, string $ownerId): array
    {
        $hash = hash('crc32', $payer->store_user_id, FALSE);

        $address = match ($currency->blockchain) {
            Blockchain::Tron => 'TKn5GuNb62KgQh7SLXznUrP33Nae' . $hash,
            Blockchain::Bitcoin => 'bc1qwzefc7fp8tdlnv0es3pk6snad22hhet5' . $hash,
            Blockchain::Ethereum => "0x8fcfbf72ad1c26c80ce194308d7ddf250e49afa7edd4" . $hash,
        };

        return [
            'address'    => $address,
            'blockchain' => $currency->blockchain
        ];
    }
}