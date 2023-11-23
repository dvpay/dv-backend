<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Store;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StorePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can update the model.
     *
     * @param  User  $user
     * @param  Store  $store
     * @return bool
     */
    public function update(User $user, Store $store): bool
    {
        return $user->id === $store->user_id;
    }

    /**
     * Determine whether the user can create the model.
     *
     * @param  User  $user
     * @param  Store  $store
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(UserRole::Root->value, UserRole::Admin->value);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  User  $user
     * @param  Store  $store
     * @return bool
     */
    public function view(User $user, Store $store): bool
    {
        return $user->id === $store->user_id;
    }

    public function bulkUpdate(User $user): bool
    {
        return $user->hasAnyRole(UserRole::Root->value, UserRole::Admin->value);
    }
}
