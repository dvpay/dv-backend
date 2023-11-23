<?php

namespace App\Http\Middleware;

use App\Exceptions\UnauthorizedException;
use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use function App\Helpers\array\nullValuesToEmptyString;

class CheckSign
{
    public function __construct(private readonly string $processingWebhookKey)
    {
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse) $next
     * @param string|null ...$guards
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $input = $request->input();

        if (is_array($input)) {
            $input = nullValuesToEmptyString($input);
        }

        $jsonBody = json_encode($input);
        $hashBody = hash('sha256', $jsonBody . $this->processingWebhookKey);

        $sign = $request->header('X-Sign');

        if ($sign !== $hashBody) {
            \Log::error("cannot validate signature", [
                'json' => $jsonBody,
                'key' => $this->processingWebhookKey,
                'ourSign' => $hashBody,
                'sign' => $sign
            ]);
            throw new UnauthorizedException(__('Invalid signature'), Response::HTTP_I_AM_A_TEAPOT);
        }

        return $next($request);
    }
}
