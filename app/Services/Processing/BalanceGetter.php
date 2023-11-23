<?php

namespace App\Services\Processing;

use App\Enums\HttpMethod;
use App\Exceptions\ProcessingException;
use App\Models\User;
use App\Services\Processing\Contracts\Client;
use Throwable;

/**
 * Temporary class, remove when balance will be correct by transactions
 */
class BalanceGetter
{
    private array $cache = [];

    public function __construct(private readonly Client $client)
    {
    }

    /**
     * @param User $user
     * @return array{"bitcoin": float, "tron": float}
     */
    public function get(User $user): array
    {
        $body = ['tron' => 0, 'btc' => 0];
        if (config('processing.fake')) {
            return $body;
        }

        if (!empty($this->cache[$user->id])) {
            return $this->cache[$user->id];
        }

        try {
            $res = $this->client->request(HttpMethod::GET, "/owners/{$user->processing_owner_id}/balances", []);

            if ($res->getStatusCode() == 200) {
                $body = json_decode((string)$res->getBody(), true);
            } else {
                $body = ['tron' => 0, 'btc' => 0];
            }
        } catch (Throwable $e) {
            $body = ['tron' => 0, 'btc' => 0];
        }

        $this->cache[$user->id] = $body;

        return $this->cache[$user->id];
    }

    /**
     * @param string $ownerId
     * @return array
     */
    public function getBalanceByOwnerStoreId(string $ownerId): array
    {
        $res = $this->client->request(HttpMethod::GET, "/owners/$ownerId/balances", []);

        if ($res->getStatusCode() != 200) {
            throw new ProcessingException(__('Balance not found.'));
        }

        if (!$result = json_decode((string)$res->getBody(), true)) {
            $result = [];
        }

        return $result;
    }

    public function getAddressBalanceByOwnerId(string $ownerId, string $blockchain = ""): array
    {
        $response = $this->client->request(HttpMethod::GET, "/owners/{$ownerId}/addresses-balance", ['blockchain' => $blockchain]);

        if ($response->getStatusCode() != 200) {
            throw new ProcessingException(__('Balance not found.'));
        }

        if (!$result = json_decode((string)$response->getBody(), true)) {
            $result = [];
        }


        return $result;
    }
}
