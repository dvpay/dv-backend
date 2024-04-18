<?php

namespace App\Http\Middleware;

use App\Enums\HttpMethod;
use App\Enums\Metric;
use App\Facades\Prometheus;
use Closure;
use Illuminate\Http\Request;

class ExecutionDurationTime
{
    /**
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        return $next($request);
    }

    public function terminate(Request $request, $response) {

        if(in_array($request->method(),[
            HttpMethod::GET->value,
            HttpMethod::POST->value,
            HttpMethod::PUT->value,
            HttpMethod::PATCH->value,
            HttpMethod::DELETE->value
        ]))
        {
            $responseTime = microtime(true) - LARAVEL_START;
            $action = $request->route()?->getActionName() ?? 'unknown';
            $method = $request->method();
            Prometheus::histogramObserve(
                Metric::BackendHttpExecutionDurationTime->getName(),
                $responseTime,
                [$action, $method]
            );
        }

    }
}
