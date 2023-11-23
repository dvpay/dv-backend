<?php
declare(strict_types=1);

namespace App\Services\Processing\Fake;

use App\Dto\ProcessingWalletDto;
use App\Enums\Blockchain;
use App\Services\Processing\Contracts\ProcessingWalletContract;
use Illuminate\Support\Str;

class ProcessingWalletFake implements ProcessingWalletContract
{
    public function getWallets(string $ownerId): array
    {
        $result = [];

        $blockchains = Blockchain::cases();
        foreach ($blockchains as $blockchain) {
            $result[] = new ProcessingWalletDto([
                "address"        => Str::random(40),
                "balance"        => "0.5",
                "blockchain"     => $blockchain->value,
                'bandwidth'      => rand(1000, 9999999),
                'bandwidthLimit' => rand(1000, 9999999),
                'energy'         => rand(1000, 9999999),
                'energyLimit'    => rand(1000, 9999999),
            ]);
        }

        return $result;
    }
}