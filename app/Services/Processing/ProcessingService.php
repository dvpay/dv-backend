<?php
declare(strict_types=1);

namespace App\Services\Processing;

use App\Dto\Transfer\TransferDto;
use App\Enums\Blockchain;
use App\Enums\HttpMethod;
use App\Exceptions\ProcessingException;
use App\Exceptions\ProcessingResultException;
use App\Services\Processing\Contracts\Client;
use App\Services\Processing\Contracts\MnemonicContract;
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
readonly class ProcessingService implements MnemonicContract, OwnerContract, TransferContract
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
     * @param int $size
     *
     * @return string
     */
    public function generate(int $size = self::SIZE): string
    {
        $res = $this->client->request(HttpMethod::GET, '/mnemonic', ['size' => (string)$size]);

        if ($res->getStatusCode() != Response::HTTP_OK) {
            throw new ProcessingException(__('Cannot get a mnemonic phrase'), $res);
        }

        $json = json_decode((string)$res->getBody(), true);

        $result = $json['result'] ?? null;
        if (!$result) {
            throw new ProcessingResultException(__('Mnemonic is empty'));
        }

        return $result;
    }

    /**
     * @param string $id
     *
     * @return string
     */
    public function createOwner(string $id): string
    {
        $res = $this->client->request(HttpMethod::POST, '/owners', ['id' => $id]);

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
    public function attachColdWalletWithAddress(Blockchain $blockchain, string $owner, array $address): array
    {
        return $this->attachWallet($blockchain, $owner, address: $address);
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
    ): array
    {
        $res = $this->client->request(
            HttpMethod::POST,
            sprintf('/owners/%s/wallets', $owner),
            [
                'blockchain' => $blockchain->value,
                'addresses'  => $address,
            ]
        );

        if ($res->getStatusCode() !== Response::HTTP_ACCEPTED) {
            throw new ProcessingException(__('Cannot attach cold wallet to owner'), $res);
        }

        $json = json_decode((string)$res->getBody(), true);
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
        $response = $this->client->request(HttpMethod::GET, '/status', []);

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
        $response = $this->client->request(HttpMethod::POST, "owners/{$dto->user->processing_owner_id}/transfer", [
            'wallet'     => $dto->addressFrom,
            'address'    => $dto->addressTo,
            'blockchain' => $dto->currency->blockchain->value,
            'owner'      => $dto->user->processing_owner_id,
            'isManual'   => false,
            'contract'   => $dto->contract,
            'uuid'       => $dto->uuid
        ]);
        Log::channel('processingLog')->error($response->getBody());

        if ($response->getStatusCode() != Response::HTTP_OK) {
            throw new Exception('Processing API response with status code: ' . $response->getStatusCode());
        }

        return true;
    }

    /**
     * @param string $owner
     * @param string $address
     * @return true
     * @throws Exception
     */
    public function resendCallback(string $owner, string $address): true
    {
        $response = $this->client->request(HttpMethod::POST, "/owners/{$owner}/addresses/resend-webhooks", [
            "address" => $address,
        ]);
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
        $response = $this->client->request(HttpMethod::PATCH, '/clients', [
            'cid' => $clientID,
            'callbackUrl' => $url,
        ]);

        if ($response->getStatusCode() !== Response::HTTP_OK) {
            throw new Exception('Processing API response with status code: ' . $response->getStatusCode());
        }
        return true;

    }
}
