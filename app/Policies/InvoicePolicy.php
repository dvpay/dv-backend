<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\Store;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class InvoicePolicy
{
    use HandlesAuthorization;

    public function view(User $user, Invoice $invoice, Store $store): bool
    {
        return $user->id === $store->user_id
            && $store->id === $invoice->store_id;
    }


}
