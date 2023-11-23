<?php

declare(strict_types=1);

namespace App\Services\ApiKey;

use App\Models\Store;
use App\Models\StoreApiKey;
use Illuminate\Database\Eloquent\Collection;

class ApiKeyService
{
    public function __construct(
        private readonly string $salt
    )
    {
    }

    public function create(Store $store): StoreApiKey
    {
        $apiKey = StoreApiKey::create([
            'store_id' => $store->id,
            'key'      => hash('sha256', $store->id . $this->salt . time()),
        ]);
        $apiKey->refresh();

        return $apiKey;
    }

    /**
     * @param Store $store
     * @return Collection StoreApiKey model
     */
    public function list(Store $store): Collection
    {
        $apiKeys = StoreApiKey::where('store_id', $store->id)->get();

        return $apiKeys;
    }

    public function updateEnabled(StoreApiKey $storeApiKey, bool $enabled): StoreApiKey
    {
        $storeApiKey->update([
            'enabled' => $enabled,
        ]);

        return $storeApiKey;
    }

    public function delete(StoreApiKey $storeApiKey): ?bool
    {
        return $storeApiKey->delete();
    }
}
