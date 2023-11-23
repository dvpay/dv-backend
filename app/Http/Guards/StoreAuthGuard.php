<?php

declare(strict_types=1);

namespace App\Http\Guards;

use App\Models\StoreApiKey;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;

class StoreAuthGuard
{
    /**
     * @throws AuthenticationException
     */
    public function __invoke(Request $request)
    {
        if (!$apiKey = $request->header('X-Api-Key')) {
            throw new AuthenticationException(__("You don't have permission to this action!"));
        }

        if (!$storeApiKey = StoreApiKey::where('key', $apiKey)->first()) {
            throw new AuthenticationException(__("You don't have permission to this action!"));
        }

        if (!$user = $storeApiKey->store->user) {
            throw new AuthenticationException(__("You don't have permission to this action!"));
        }

        return $user;
    }
}