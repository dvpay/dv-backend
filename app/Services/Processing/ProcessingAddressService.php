<?php

declare(strict_types=1);

namespace App\Services\Processing;

use App\Enums\Blockchain;
use App\Enums\HttpMethod;
use App\Exceptions\ProcessingException;
use App\Exceptions\ProcessingResultException;
use App\Models\Currency;
use App\Models\Invoice;
use App\Models\Payer;
use App\Services\Processing\Contracts\AddressContract;
use App\Services\Processing\Contracts\Client;
use App\Services\Processing\Dto\Watch;
use App\Services\Processing\Dto\WatchPromise;
use DateTime;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;

class ProcessingAddressService implements AddressContract
{
    public function __construct(
            private readonly Client $client,
            private readonly int    $tronWatchMultiplier,
            private readonly int    $bitcoinWatchMultiplier,
    ) {
    }

    public function generate(Blockchain $blockchain, string $owner): string
    {
        $res = $this->client->request(
                HttpMethod::POST,
                sprintf('/owners/%s/addresses', $owner),
                ['blockchain' => $blockchain->value]
        );

        if ($res->getStatusCode() !== Response::HTTP_CREATED) {
            throw new ProcessingException(__('Cannot generate temporary address'), $res);
        }

        $json = json_decode((string) $res->getBody(), true);

        $address = $json['result']['address'] ?? null;
        if (!$address) {
            throw new ProcessingResultException(__('Address is empty'));
        }

        return $address;
    }

    public function generateAndWatch(Watch $watch, Invoice $invoice): WatchPromise
    {
        $req = [
                'blockchain' => $watch->blockchain->value,
                'invoice_id' => $invoice->id,
                'watch'      => [
                        'duration'        => $this->calcDuration($watch),
                        'contractAddress' => $watch->contractAddress,
                        'amount'          => (float) $watch->amount,
                ],
        ];
        $res = $this->client->request(
                HttpMethod::POST,
                sprintf('/owners/%s/addresses', $watch->owner),
                $req,
        );

        if ($res->getStatusCode() !== Response::HTTP_CREATED) {
            throw new ProcessingException(__('Cannot generate temporary address and watch for that'), $res);
        }

        return $this->parseResponse($res);
    }

    public function watch(Watch $watch): WatchPromise
    {
        if ($watch->address === '') {
            throw new ProcessingException(__('Address cannot be empty'));
        }

        $res = $this->client->request(
                HttpMethod::POST,
                '/watches',
                [
                        'address'         => $watch->address,
                        'blockchain'      => $watch->blockchain,
                        'ownerId'         => $watch->owner,
                        'contractAddress' => $watch->contractAddress,
                        'duration'        => $this->calcDuration($watch),
                        'amount'          => (float) $watch->amount,
                ],
        );

        if ($res->getStatusCode() !== Response::HTTP_CREATED) {
            throw new ProcessingException(__('Cannot watch for address'), $res);
        }

        return $this->parseResponse($res);
    }

    /**
     * @param  mixed  $res
     * @return WatchPromise
     */
    public function parseResponse(ResponseInterface $res): WatchPromise
    {
        $json = json_decode((string) $res->getBody(), true);

        $address = $json['result']['address'] ?? '';
        $expiredAt = $json['result']['expiredAt'] ?? null;
        $watchId = $json['result']['watchId'] ?? '';

        if (!$address || !$expiredAt || !$watchId) {
            throw new ProcessingResultException(__('Response is broken'));
        }

        $expiredAt = DateTime::createFromFormat(DATE_ATOM, $expiredAt);
        if ($expiredAt === false) {
            throw new ProcessingResultException(__('expiredAt is not datetime'));
        }

        return new WatchPromise(
                address  : $address,
                watchId  : $watchId,
                expiredAt: $expiredAt
        );
    }

    private function calcDuration(Watch $watch): int
    {
        return match ($watch->blockchain) {
            Blockchain::Bitcoin => $watch->duration * $this->bitcoinWatchMultiplier,
            Blockchain::Tron => $watch->duration * $this->tronWatchMultiplier,
        };
    }

    public function getAll(string $ownerId): array
    {
	    $response = $this->client->request(HttpMethod::GET, "/owners/{$ownerId}/addresses-balance", []);

        if ($response->getStatusCode() != Response::HTTP_OK) {
            throw new ProcessingResultException(__('Addresses not found.'));
        }

        return json_decode((string) $response->getBody(), true);
    }

    public function getStaticAddress(Currency $currency, Payer $payer, string $ownerId): array
    {
        $response = $this->client->request(HttpMethod::POST, "/owners/{$ownerId}/addresses/permanent", [
                'payerId'    => $payer->id,
                'blockchain' => $currency->blockchain
        ]);
        $json = json_decode((string) $response->getBody(), true);
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
