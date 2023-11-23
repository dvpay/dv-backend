<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Invite;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class InvitePolicy
{
    use HandlesAuthorization;

    public function viewAny()
    {
        return true;
    }

    public function view(User $user, Invite $invite): bool
    {
        return $user->id === $invite->user_id || $user->hasRole(UserRole::Root->value);
    }
    public function update(User $user, Invite $invite): bool
    {
        return $user->id === $invite->user_id || $user->hasRole(UserRole::Root->value);
    }

}