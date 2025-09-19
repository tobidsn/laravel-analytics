<?php

namespace Tobidsn\LaravelAnalytics\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;
use Tobidsn\LaravelAnalytics\AnalyticsServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Tobidsn\\LaravelAnalytics\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );

        // Add dummy auth routes for testing
        $this->app['router']->get('login', function () {
            return response('Login Page');
        })->name('login');

        $this->app['router']->post('logout', function () {
            return response('Logged Out');
        })->name('logout');
    }

    protected function getPackageProviders($app)
    {
        return [
            AnalyticsServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('app.key', 'base64:'.base64_encode(str_repeat('a', 32)));
        config()->set('database.default', 'testing');

        // Cache configuration for testing
        config()->set('cache.default', 'array');
        config()->set('cache.stores.array', [
            'driver' => 'array',
            'serialize' => false,
        ]);
        config()->set('cache.stores.default', [
            'driver' => 'array',
            'serialize' => false,
        ]);

        // Analytics configuration
        config()->set('analytics.property_id', 'test-property-id');
        config()->set('analytics.service_account_credentials_json', __DIR__.'/fixtures/test-credentials.json');
        config()->set('analytics.cache.duration', 3600);
        config()->set('analytics.cache.store', null); // Use default cache store
        config()->set('analytics.cache.prefix', 'analytics:');
        config()->set('analytics.dashboard.enabled', true);
        config()->set('analytics.dashboard.middleware', ['web']);
        config()->set('analytics.dashboard.route_prefix', 'analytics');
        config()->set('analytics.api.enabled', true);
        config()->set('analytics.api.middleware', ['api']);
        config()->set('analytics.api.route_prefix', 'analytics/api');
        config()->set('analytics.assets_path', 'vendor/analytics'); // Default assets path

        /*
        $migration = include __DIR__.'/../database/migrations/create_skeleton_table.php.stub';
        $migration->up();
        */
    }
}
