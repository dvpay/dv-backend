<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserInvoiceAddress;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserInvoiceAddressPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        
    }

    public function view(User $user, UserInvoiceAddress $address): bool
    {
        return $user->processing_owner_id === $address->processing_owner_id;
    }

    public function create(User $user): bool
    {
    }

    public function update(User $user, UserInvoiceAddress $address): bool
    {
    }

    public function delete(User $user, UserInvoiceAddress $address): bool
    {
    }

    public function restore(User $user, UserInvoiceAddress $address): bool
    {
    }

    public function forceDelete(User $user, UserInvoiceAddress $address): bool
    {
    }
}
