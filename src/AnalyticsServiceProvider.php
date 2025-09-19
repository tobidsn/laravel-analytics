<?php

namespace Tobidsn\LaravelAnalytics;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Tobidsn\LaravelAnalytics\Commands\AnalyticsCommand;
use Tobidsn\LaravelAnalytics\Commands\AnalyticsInstallCommand;
use Tobidsn\LaravelAnalytics\Services\AnalyticsCacheService;
use Tobidsn\LaravelAnalytics\Services\AnalyticsService;
use Tobidsn\LaravelAnalytics\Services\AnalyticsStrategyFactory;
use Tobidsn\LaravelAnalytics\Services\GoogleAnalyticsClientFactory;

class AnalyticsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-analytics')
            ->hasConfigFile('analytics')
            ->hasViews()
            ->hasRoute('web')
            ->hasRoute('api')
            ->hasCommands([
                AnalyticsCommand::class,
                AnalyticsInstallCommand::class,
            ]);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(GoogleAnalyticsClientFactory::class, function ($app) {
            return new GoogleAnalyticsClientFactory(
                config('analytics.property_id'),
                config('analytics.service_account_credentials_json')
            );
        });

        $this->app->singleton(AnalyticsCacheService::class, function ($app) {
            $cacheStore = config('analytics.cache.store');

            // Get the cache repository - always call store() to get the Repository interface
            $cache = $app['cache']->store($cacheStore);

            return new AnalyticsCacheService(
                $cache,
                config('analytics.cache.duration'),
                config('analytics.cache.prefix')
            );
        });

        $this->app->singleton(AnalyticsStrategyFactory::class);

        $this->app->singleton(AnalyticsService::class, function ($app) {
            return new AnalyticsService(
                $app->make(GoogleAnalyticsClientFactory::class)
            );
        });
    }

    public function packageBooted(): void
    {
        if (config('analytics.dashboard.enabled')) {
            $this->registerWebRoutes();
        }

        if (config('analytics.api.enabled')) {
            $this->registerApiRoutes();
        }

        $this->defineAssetPublishing();
        $this->registerBladeDirectives();
    }

    protected function registerBladeDirectives(): void
    {
        // Register @analyticsAsset directive for versioned assets
        Blade::directive('analyticsAsset', function ($file) {
            $file = trim($file, "'\"");

            return "<?php
                \$assetsPath = config('analytics.assets_path', 'vendor/analytics');
                \$publicAssetsPath = public_path(\$assetsPath);
                \$packageAssetsPath = base_path('vendor/tobidsn/laravel-analytics/public/vendor/analytics');

                // Check if assets are published to public directory
                if (is_dir(\$publicAssetsPath)) {
                    \$manifestPath = \$publicAssetsPath . '/.vite/manifest.json';

                    if (file_exists(\$manifestPath)) {
                        \$manifest = json_decode(file_get_contents(\$manifestPath), true);

                        // Map common asset names to manifest keys
                        \$assetMap = [
                            'app.js' => 'resources/js/app.jsx',
                            'app.css' => 'style.css'
                        ];

                        \$manifestKey = \$assetMap['{$file}'] ?? '{$file}';

                        if (isset(\$manifest[\$manifestKey])) {
                            \$versionedFile = \$manifest[\$manifestKey]['file'];
                            echo asset(\$assetsPath . '/' . \$versionedFile);
                        } else {
                            echo asset(\$assetsPath . '/' . '{$file}');
                        }
                    } else {
                        // Try to find hashed files in published directory
                        \$extension = pathinfo('{$file}', PATHINFO_EXTENSION);
                        \$pattern = \$publicAssetsPath . '/app-*.' . \$extension;
                        \$files = glob(\$pattern);
                        if (!empty(\$files)) {
                            \$file = basename(\$files[0]);
                            echo asset(\$assetsPath . '/' . \$file);
                        } else {
                            echo asset(\$assetsPath . '/' . '{$file}');
                        }
                    }
                } else {
                    // Assets not published, serve from package route
                    echo route('analytics.assets', '{$file}');
                }
            ?>";
        });
    }

    protected function registerWebRoutes(): void
    {
        Route::middleware(config('analytics.dashboard.middleware'))
            ->prefix(config('analytics.dashboard.route_prefix'))
            ->group($this->package->basePath('/../routes/web.php'));
    }

    protected function registerApiRoutes(): void
    {
        Route::middleware(config('analytics.api.middleware'))
            ->prefix(config('analytics.api.route_prefix'))
            ->group($this->package->basePath('/../routes/api.php'));
    }

    protected function defineAssetPublishing(): void
    {
        // Publish analytics assets (CSS, JS, images) to public directory
        $this->publishes([
            $this->package->basePath('/../public/vendor/analytics') => public_path(config('analytics.assets_path', 'vendor/analytics')),
        ], 'analytics-assets');

        // Publish analytics views for customization
        $this->publishes([
            $this->package->basePath('/../resources/views') => resource_path('views/vendor/analytics'),
        ], 'analytics-views');

        // Publish analytics configuration
        $this->publishes([
            $this->package->basePath('/../config/analytics.php') => config_path('analytics.php'),
        ], 'analytics-config');

        // Publish all analytics files at once
        $this->publishes([
            $this->package->basePath('/../public/vendor/analytics') => public_path(config('analytics.assets_path', 'vendor/analytics')),
            $this->package->basePath('/../resources/views') => resource_path('views/vendor/analytics'),
            $this->package->basePath('/../config/analytics.php') => config_path('analytics.php'),
        ], 'analytics');
    }
}
