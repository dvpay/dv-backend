<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Store;
use App\Models\StoreApiKey;

class StoreRepository
{
    public function getStoreByApiKey($storeApiKey): ?Store
    {
        $storeApiKey = StoreApiKey::where('key', $storeApiKey)->first();

        return $storeApiKey->store;
    }

    public function getStoreById(string $storeId): ?Store
    {
        return Store::find($storeId);
    }
}