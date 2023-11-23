<?php

declare(strict_types=1);

namespace App\Services\Processing\Fake;

use App\Enums\Blockchain;
use App\Exceptions\ProcessingException;
use App\Models\Currency;
use App\Models\Invoice;
use App\Models\Payer;
use App\Services\Processing\Contracts\AddressContract;
use App\Services\Processing\Dto\Watch;
use App\Services\Processing\Dto\WatchPromise;

class AddressFake implements AddressContract
{

    public function generate(Blockchain $blockchain, string $owner): string
    {
        return match ($blockchain) {
            Blockchain::Tron => fake()->lexify('TKn5GuNb62KgQh7SLXznUrP33Nae??????'),
            Blockchain::Bitcoin => fake()->lexify('bc1qwzefc7fp8tdlnv0es3pk6snad22hhet5??????'),
        };
    }

    public function generateAndWatch(Watch $watch, Invoice $invoice): WatchPromise
    {
        $address = $this->generate($watch->blockchain, $watch->owner);
        $watch = $watch->withAddress($address);
        return $this->watch($watch);
    }

    public function watch(Watch $watch): WatchPromise
    {
        if (!$watch->address) {
            throw new ProcessingException(__('Address cannot be empty'));
        }

        $duration = $watch->duration;
        if ($watch->duration === 0) {
            $duration = fake()->numberBetween(1000, 9999);
        }


        $expiredAt = new \DateTime();
        $expiredAt->add(new \DateInterval("PT{$duration}S"));

        return new WatchPromise(
                address  : $watch->address,
                watchId  : fake()->uuid(),
                expiredAt: $expiredAt
        );
    }

    public function getAll(string $ownerId): array
    {
        return [
                [
                        "blockchain" => "tron",
                        "address"    => fake()->lexify('TKn5GuNb62KgQh7SLXznUrP33Nae??????'),
                        "balance"    => rand(0, 50000),
                        "state"      => "free",
                        "watch"      => null,
                        "type"       => "sc",
                ],
                [
                        "blockchain" => "tron",
                        "address"    => fake()->lexify('TKn5GuNb62KgQh7SLXznUrP33Nae??????'),
                        "balance"    => rand(0, 50000),
                        "state"      => "busy",
                        "watch"      => [
                                "id"     => "db14f43f-4031-475a-a0de-46a128ac26d4",
                                "status" => "caught",
                        ],
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
        };

        return [
                'address'    => $address,
                'blockchain' => $currency->blockchain
        ];
    }
}