<?php
declare(strict_types=1);

namespace App\Services\Processing;

use App\Dto\Transfer\TransferDto;
use App\Enums\Blockchain;
use App\Enums\HttpMethod;
use App\Exceptions\ApiException;
use App\Exceptions\Processing\QueueException;
use App\Exceptions\Processing\ResourceException;
use App\Exceptions\ProcessingException;
use App\Exceptions\ProcessingResultException;
use App\Services\Processing\Contracts\Client;
use App\Services\Processing\Contracts\HeartbeatContract;
use App\Services\Processing\Contracts\OwnerContract;
use App\Services\Processing\Contracts\TransferContract;
use Exception;
use Illuminate\Support\Facades\Log;
use JetBrains\PhpStorm\Deprecated;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * ProcessingService
 */
readonly class ProcessingService implements OwnerContract, TransferContract, HeartbeatContract
{
    /**
     * @param Client $client
     */
    public function __construct(
        private Client $client,
    )
    {
    }

    /**
     * @param string $id
     *
     * @return string
     */
    public function createOwner(string $id): string
    {
        $res = $this->client->request(
            method: HttpMethod::POST,
            uri: '/owners',
            data: ['id' => $id],
            statsUri: '/owners'
        );

        if ($res->getStatusCode() != Response::HTTP_CREATED) {
            throw new ProcessingException(__('Cannot create owner'), $res);
        }

        $json = json_decode((string)$res->getBody(), true);

        $result = $json['result']['id'] ?? null;
        if (!$result) {
            throw new ProcessingResultException(__('Owner id is empty'));
        }

        return $result;
    }

    /**
     * @param Blockchain $blockchain
     * @param string $owner
     * @param string $address
     *
     * @return string
     */
    public function attachColdWalletWithAddress(Blockchain $blockchain, string $owner, array $address, ?string $validateCode = null): array
    {
        return $this->attachWallet(blockchain: $blockchain, owner: $owner, address: $address, validateCode: $validateCode);
    }

    /**
     * @param Blockchain $blockchain
     * @param string $owner
     * @param string $address
     * @param string $mnemonic
     * @param string $passphrase
     * @param string $privateKey
     *
     * @return string
     */
    private function attachWallet(
        Blockchain $blockchain,
        string     $owner,
        array      $address = [],
        ?string    $validateCode = null,
    ): array
    {
        $requestParams = [
            'blockchain' => $blockchain->value,
            'addresses'  => $address,
        ];

        if ($validateCode !== null) {
            $requestParams['validationCode'] = $validateCode;
        }

        $res = $this->client->request(
            method: HttpMethod::POST,
            uri: sprintf('/owners/%s/wallets', $owner),
            data: $requestParams,
            statsUri: '/owners/{ownerId}/wallets'
        );
        $json = json_decode((string)$res->getBody(), true);

        if ($res->getStatusCode() === Response::HTTP_BAD_REQUEST && is_null($validateCode) && $json['error'] === 'the confirmation code not valid') {
            return ['codeSend' => true];
        }

        if ($res->getStatusCode() !== Response::HTTP_ACCEPTED) {
            throw new ApiException($json['error'], 400);
        }

        $codeSend = $json['result']['message'] ?? null;

        if ($codeSend && $validateCode == null) {
            return ['codeSend' => $codeSend];
        }
        $address = $json['result']['addresses'] ?? null;
        if (!$address) {
            throw new ProcessingResultException(__('Wallet address is empty'));
        }

        return $address;
    }

    /**
     * @param string $owner
     * @param Blockchain $blockchain
     * @param bool $isManual
     * @param string $contract
     *
     */
    #[Deprecated] // deprecated but him not have normal types
    public function doTransfer(string $owner, Blockchain $blockchain, bool $isManual, string $address, string $contract = ''): false
    {
        return false;
    }

    /**
     * @return ResponseInterface
     * @throws Exception
     */
    public function getStatusService(): ResponseInterface
    {
        $response = $this->client->request(
            method: HttpMethod::GET,
            uri: '/status',
            data: [],
            statsUri: '/status'
        );

        if ($response->getStatusCode() != Response::HTTP_OK) {
            throw new Exception('Processing API response with status code: ' . $response->getStatusCode());
        }

        return $response;
    }


    /**
     * Transfers funds from processing address to owner wallet.
     * @param TransferDto $dto
     * @return true
     * @throws Exception
     */
    public function transferFromAddress(TransferDto $dto): bool
    {
        $response = $this->client->request(
            method: HttpMethod::POST,
            uri: "owners/{$dto->user->processing_owner_id}/transfer",
            data: [
                'wallet'     => $dto->addressFrom,
                'address'    => $dto->addressTo,
                'blockchain' => $dto->currency->blockchain->value,
                'owner'      => $dto->user->processing_owner_id,
                'isManual'   => false,
                'contract'   => $dto->contract,
                'uuid'       => $dto->uuid,
                'amount'     => $dto->amount,
            ],
            statsUri: 'owners/{ownerId}/transfer'
        );
        Log::channel('processingLog')->error($response->getBody());
        Log::channel('processingLog')->error('DTO', [$dto]);

        if ($response->getStatusCode() != Response::HTTP_OK) {
            $json = json_decode((string)$response->getBody(), true);

            if ($json['result'] === 'busy') {
                throw new QueueException();
            }
            if ($json['result'] === 'error' && $json['error'] === 'calculate capacity: queue: no resources available') {
                throw new ResourceException();
            }
            throw new Exception('Processing API response with status code: ' . $response->getStatusCode());
        }

        return true;
    }

    /**
     * Transfers funds from processing address to owner wallet.
     * @param TransferDto $dto
     * @return true
     * @throws Exception
     */
    public function transferFromProcessing(TransferDto $dto): bool
    {

        if (Blockchain::Tron !== $dto->currency->blockchain) {
            throw new ApiException('It supports nly TRON chain', 422);
        }

        $response = $this->client->request(
            method: HttpMethod::POST,
            uri: "owners/{$dto->user->processing_owner_id}/tron/withdrawal",
            data: [
                'uuid'      => $dto->uuid,
                'addressTo' => $dto->addressTo,
                'contract'  => $dto->contract,
                'amount'    => (string)$dto->amount,
            ],
            statsUri: 'owners/{ownerId}/tron/withdrawal'
        );

        Log::channel('processingLog')->error($response->getBody());
        Log::channel('processingLog')->error('DTO', [$dto]);

        if ($response->getStatusCode() != Response::HTTP_OK) {
            $json = json_decode((string)$response->getBody(), true);

            if ($json['result'] === 'busy') {
                throw new QueueException();
            }

            if ($json['error'] === 'calculate capacity: queue: no resources available') {
                throw new ResourceException();
            }

            throw new Exception('Processing API response with status code: ' . $response->getStatusCode());
        }

        return true;
    }

    /**
     * depreceted?
     * @param string $owner
     * @param string $address
     * @return true
     * @throws Exception
     */
    public function resendCallback(string $owner, string $address, array $transactions): true
    {
        $response = $this->client->request(
            method: HttpMethod::POST,
            uri: "/owners/{$owner}/addresses/resend-webhooks",
            data: [
                "address" => $address,
                "hashes"  => $transactions,
            ],
            statsUri: '/owners/{ownerId}/addresses/resend-webhooks'
        );
        Log::channel('processingLog')->error($response->getBody());
        if ($response->getStatusCode() !== Response::HTTP_OK) {
            throw new Exception('Processing API response with status code: ' . $response->getStatusCode());
        }
        return true;
    }

    /**
     * @throws Exception
     */
    public function syncTransactions(string $owner, string $address, array $transactions, Blockchain $blockchain): true
    {
        $response = $this->client->request(
            method: HttpMethod::POST,
            uri: "/owners/{$owner}/transactions/sync",
            data: [
                "address"      => $address,
                "blockchain"   => $blockchain->value,
                "transactions" => $transactions,
            ],
            statsUri: '/owners/{ownerId}/transactions/sync'
        );
        Log::channel('processingLog')->error($response->getBody());
        if ($response->getStatusCode() !== Response::HTTP_OK) {
            throw new Exception('Processing API response with status code: ' . $response->getStatusCode());
        }
        return true;
    }

    /**
     * @param string $clientID
     * @param string $url
     * @return true
     * @throws Exception
     */
    public function updateCallbackUrl(string $clientID, string $url): true
    {
        $response = $this->client->request(
            method: HttpMethod::PATCH,
            uri: '/clients',
            data: [
                'cid'         => $clientID,
                'callbackUrl' => $url,
            ],
            statsUri: '/clients'
        );

        if ($response->getStatusCode() !== Response::HTTP_OK) {
            throw new Exception('Processing API response with status code: ' . $response->getStatusCode());
        }
        return true;
    }

    /**
     * @param string $owner
     * @return array
     * @throws Exception
     */
    public function telegramDeepLink(string $owner): array
    {
        $response = $this->client->request(
            method: HttpMethod::GET,
            uri: "/owners/{$owner}/tg-deeplink",
            data: [],
            statsUri: '/owners/{ownerId}/tg-deeplink'
        );

        if ($response->getStatusCode() !== Response::HTTP_OK) {
            throw new Exception('Processing API response with status code: ' . $response->getStatusCode());
        }
        $json = json_decode((string)$response->getBody(), true);

        return $json['result'];
    }
}
