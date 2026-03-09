<?php

namespace App\Providers;

use App\Auth\BridgeUserProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Auth::provider('bridge', function ($app, array $config): BridgeUserProvider {
            $serviceConfig = (array) config('services.auth_bridge', []);

            $endpoint = trim((string) ($config['bridge_endpoint'] ?? $serviceConfig['url'] ?? ''));
            $token = trim((string) ($config['bridge_token'] ?? $serviceConfig['token'] ?? ''));
            $timeout = (int) ($config['bridge_timeout'] ?? $serviceConfig['timeout'] ?? 8);

            if (! Str::startsWith($endpoint, ['http://', 'https://'])) {
                $appUrl = (string) config('app.url', '');
                if ($appUrl === '' && env('VERCEL_URL')) {
                    $appUrl = 'https://'.trim((string) env('VERCEL_URL'));
                }

                $endpoint = rtrim($appUrl, '/').'/api/bridge-auth';
            }

            return new BridgeUserProvider(
                endpoint: $endpoint,
                token: $token,
                timeoutSeconds: max(1, $timeout)
            );
        });

        // Vercel/proxy environments can resolve request scheme as HTTP.
        // Force HTTPS so Vite/assets are generated with secure URLs.
        if (
            $this->app->environment('production')
            || filter_var(env('FORCE_HTTPS', false), FILTER_VALIDATE_BOOL)
            || env('VERCEL')
        ) {
            URL::forceScheme('https');
        }
    }
}
