<?php

namespace App\Policies;

use App\Models\Store;
use App\Models\User;
use App\Models\Webhook;
use Illuminate\Auth\Access\HandlesAuthorization;

class WebhookPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can update the model.
     *
     * @param  User  $user
     * @param  Store  $store
     * @return bool
     */
    public function update(User $user, Webhook $webhook, Store $store)
    {
        return $user->id === $store->user_id
            && $store->id === $webhook->store_id;
    }
}
