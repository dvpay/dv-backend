<?php

declare(strict_types=1);

namespace App\Services\Processing;

use App\Enums\HttpMethod;
use App\Exceptions\ProcessingResultException;
use App\Models\Currency;
use App\Models\Payer;
use App\Services\Processing\Contracts\AddressContract;
use App\Services\Processing\Contracts\Client;
use Symfony\Component\HttpFoundation\Response;

readonly class ProcessingAddressService implements AddressContract
{
    public function __construct(
        private Client $client,
    )
    {
    }


    public function getAll(string $ownerId): array
    {
        $response = $this->client->request(
            method: HttpMethod::GET,
            uri: "/owners/{$ownerId}/addresses-balance",
            data: [],
            statsUri: '/owners/{ownerId}/addresses-balance'
        );

        if ($response->getStatusCode() != Response::HTTP_OK) {
            throw new ProcessingResultException(__('Addresses not found.'));
        }

        return json_decode((string)$response->getBody(), true);
    }

    public function getStaticAddress(Currency $currency, Payer $payer, string $ownerId): array
    {
        $response = $this->client->request(
            method: HttpMethod::POST,
            uri: "/owners/{$ownerId}/addresses/permanent",
            data: [
                'payerId'    => $payer->id,
                'blockchain' => $currency->blockchain,
                'segWit'     => true,
            ],
            statsUri: '/owners/{ownerId}/addresses/permanent'
        );
        $json = json_decode((string)$response->getBody(), true);
        $address = $json['result']['address'] ?? '';

        if (!$address) {
            throw new ProcessingResultException(__('Response is broken'));
        }

        return [
            'address'    => $address,
            'blockchain' => $currency->blockchain
        ];
    }
}
