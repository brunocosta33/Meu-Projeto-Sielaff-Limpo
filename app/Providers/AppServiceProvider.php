<?php

namespace App\Providers;

use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    
    
    
    public function boot(Request $request)
    {
        Paginator::useBootstrap();

        $forwardedProto = strtolower((string) $request->headers->get('x-forwarded-proto', ''));
        $configuredUrl = strtolower((string) config('app.url', ''));
        $shouldUseHttps = $request->isSecure()
            || $forwardedProto === 'https'
            || str_starts_with($configuredUrl, 'https://');

        if ($shouldUseHttps) {
            URL::forceScheme('https');

            // Keep auth/session cookies valid when the app is behind HTTPS/reverse proxies.
            config([
                'session.secure' => true,
            ]);
        }
    }

}
