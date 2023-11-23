<?php

namespace App\Policies;

use App\Models\Store;
use App\Models\StoreApiKey;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StoreApiKeyPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\StoreApiKey  $storeApiKey
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, StoreApiKey $storeApiKey, Store $store)
    {
        return $user->id === $store->user_id
            && $store->id === $storeApiKey->store_id;
    }
}
