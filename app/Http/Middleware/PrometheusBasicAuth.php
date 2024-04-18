<?php

namespace App\Http\Middleware;

use App\Exceptions\UnauthorizedException;
use Illuminate\Http\Request;

class PrometheusBasicAuth
{

    public function handle(Request $request, \Closure $next, ...$guards)
    {
        $user = config('prometheus.basicAuthLogin');
        $pass = config('prometheus.basicAuthPassword');

        if(empty($user) || empty($pass)) {
            return $next($request);
        }

        $clientUser = $request->server('PHP_AUTH_USER');
        $clientPass = $request->server('PHP_AUTH_PW');

        if( $user != $clientUser || $pass != $clientPass) {
            throw new UnauthorizedException();
        }

        return $next($request);
    }
}
