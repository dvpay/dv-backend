<?php

namespace App\Http\Middleware;

use App\Exceptions\UnauthorizedException;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function redirectTo($request): void
    {
        if (! $request->expectsJson()) {
            throw new UnauthorizedException(__("You don't have permission to this action!"));
        }
    }
}
