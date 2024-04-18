<?php

declare(strict_types=1);

namespace App\Services\Processing;

use App\Dto\ProcessingWalletDto;
use App\Enums\Blockchain;
use App\Enums\HttpMethod;
use App\Exceptions\ProcessingException;
use App\Services\Processing\Contracts\Client;
use App\Services\Processing\Contracts\ProcessingWalletContract;
use Symfony\Component\HttpFoundation\Response;

class ProcessingWalletService implements ProcessingWalletContract
{
    public function __construct(private readonly Client $client)
    {
    }

    public function getWallets(string $ownerId): array
    {
        $uri = "/owners/$ownerId/processing-wallets";

        $response = $this->client->request(
            method: HttpMethod::GET,
            uri: $uri,
            data: [],
            statsUri: '/owners/{ownerId}/processing-wallets'
        );

        if ($response->getStatusCode() !== Response::HTTP_OK) {
            throw new ProcessingException(__('Cannot get processing wallets'), $response->withStatus(400));
        }

        $res = json_decode((string)$response->getBody(), true);

        $result = [];

        foreach ($res['result'] as $key => $value) {
            $result[] = new ProcessingWalletDto([
                'blockchain'     => $key,
                'address'        => $value['address'],
                'transferType'   => $value['transferType'],
                'balance'        => number_format($value['balance'], 8, '.', ''),
                'bandwidth'      => number_format($value['bandwidth'], 0, '.', ''),
                'bandwidthLimit' => number_format($value['bandwidth_limit'], 0, '.', ''),
                'energy'         => number_format($value['energy'], 0, '.', ''),
                'energyLimit'    => number_format($value['energy_limit'], 0, '.', ''),
            ]);
        }

        return $result;
    }

    public function switchType(string $ownerId, string $blockchain, string $type): true
    {
        $response = $this->client->request(
            method: HttpMethod::POST,
            uri:"/owners/$ownerId/processing-wallets/switch-type",
            data: [
                'blockchain' => $blockchain,
                'type' => $type
            ],
            statsUri:  '/owners/{ownerId}/processing-wallets/switch-type'
        );

        if ($response->getStatusCode() !== Response::HTTP_OK) {
            throw new ProcessingException(__('Cannot switch processing wallets'), $response->withStatus(400));
        }
        return true;
    }

}