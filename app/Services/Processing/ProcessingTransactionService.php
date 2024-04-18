<?php

declare(strict_types=1);

namespace App\Services\Processing;

use App\Dto\ProcessingTransactionInfoDto;
use App\Enums\Blockchain;
use App\Enums\HttpMethod;
use App\Exceptions\ApiException;
use App\Exceptions\ProcessingException;
use App\Services\Processing\Contracts\Client;
use App\Services\Processing\Contracts\TransactionContract;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class ProcessingTransactionService implements TransactionContract
{
    public function __construct(private Client $client)
    {
    }

    public function info(string $txId): ProcessingTransactionInfoDto
    {
        $response = $this->client->request(
            method: HttpMethod::GET,
            uri: "/transactions/$txId",
            data: [],
            statsUri: '/transactions/{txId}'
        );
        $data = json_decode((string)$response->getBody(), true);

        if ($response->getStatusCode() != Response::HTTP_OK) {
            throw new ApiException(message: $data['error'], code: 400);
        }

        $data['blockchain'] = Blockchain::tryFrom($data['blockchain']);

        return new ProcessingTransactionInfoDto($data);
    }

    public function getTransactionByAddress(string $ownerId, string $address): array
    {
        $response = $this->client->request(
            method: HttpMethod::GET,
            uri: "/owners/$ownerId/addresses/$address/transactions",
            data: [],
            statsUri: '/owners/{ownerId}/addresses/{address}/transactions'
        );
        $data = json_decode((string)$response->getBody(), true);

        if ($response->getStatusCode() != Response::HTTP_OK) {
            throw new ApiException(message: $data['error'], code: 400);
        }

        return $data;
    }
}
