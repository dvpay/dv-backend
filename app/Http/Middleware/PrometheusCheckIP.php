<?php

namespace App\Http\Middleware;

use App\Exceptions\UnauthorizedException;
use Illuminate\Http\Request;

class PrometheusCheckIP
{

    public function handle(Request $request, \Closure $next, ...$guards)
    {
        $allowedIps = config('prometheus.allowed_ips', []);

        if (!count($allowedIps)) {
            return $next($request);
        }

        if(in_array($request->ip(),$allowedIps))
        {
            return $next($request);
        }

        throw new UnauthorizedException(__("You don't have permission to this action!"));
    }
}
