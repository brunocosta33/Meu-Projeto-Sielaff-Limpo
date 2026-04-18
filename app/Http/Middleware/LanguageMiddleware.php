<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;


class LanguageMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $supportedLocales = ['pt', 'en', 'de', 'fr'];
        $language = session('language', session('locale', config('app.locale')));

        if (! in_array($language, $supportedLocales, true)) {
            $language = config('app.fallback_locale', 'pt');
        }

        app()->setLocale($language);

        return $next($request);
    }
}
