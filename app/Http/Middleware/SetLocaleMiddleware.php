<?php

namespace App\Http\Middleware;

use Closure;

class SetLocaleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $supportedLocales = ['pt', 'en', 'de', 'fr'];

        if ($request->has('locale')) {
            $locale = strtolower((string) $request->locale);
            $locale = in_array($locale, $supportedLocales, true) ? $locale : 'pt';

            session()->put('locale', $locale);
            session()->put('language', $locale);
        }

        $locale = session('locale', session('language', config('app.locale')));

        if (! in_array($locale, $supportedLocales, true)) {
            $locale = config('app.fallback_locale', 'pt');
        }

        session()->put('locale', $locale);
        session()->put('language', $locale);
        app()->setLocale($locale);

        return $next($request);
    }
}
