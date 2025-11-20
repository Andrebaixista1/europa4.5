<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\PackageManifest;

class VercelServiceProvider extends ServiceProvider
{
    /**
     * Register services for Vercel serverless environment.
     */
    public function register()
    {
        // Override PackageManifest to use /tmp for cache
        $this->app->singleton(PackageManifest::class, function ($app) {
            $manifestPath = '/tmp/bootstrap/cache/packages.php';
            
            // Ensure directory exists
            if (!file_exists(dirname($manifestPath))) {
                mkdir(dirname($manifestPath), 0755, true);
            }
            
            return new PackageManifest(
                new \Illuminate\Filesystem\Filesystem,
                $app->basePath(),
                $manifestPath
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot()
    {
        //
    }
}
