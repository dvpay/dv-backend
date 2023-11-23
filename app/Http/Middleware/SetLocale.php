<?php

namespace App\Http\Middleware;

use App\Enums\Locale;
use Closure;
use Illuminate\Http\Request;

/**
 * SetLocale
 */
class SetLocale
{
    /**
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $locales = Locale::asArray();

        $locale = $request->getPreferredLanguage($locales);

        app()->setLocale($locale);

        return $next($request);
    }
}
