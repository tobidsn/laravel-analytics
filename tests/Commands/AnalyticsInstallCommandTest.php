<?php

namespace Tobidsn\LaravelAnalytics\Tests\Commands;

use Illuminate\Support\Facades\File;
use Tobidsn\LaravelAnalytics\Tests\TestCase;

class AnalyticsInstallCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Clean up any existing published files
        $this->cleanupPublishedFiles();
    }

    protected function tearDown(): void
    {
        // Clean up after tests
        $this->cleanupPublishedFiles();

        parent::tearDown();
    }

    /** @test */
    public function it_can_install_default_components()
    {
        $this->artisan('analytics:install')
            ->expectsOutput('🚀 Installing Laravel Analytics Package...')
            ->expectsOutput('📦 Publishing analytics assets...')
            ->expectsOutput('⚙️  Publishing analytics configuration...')
            ->doesntExpectOutput('🎨 Publishing analytics views...')
            ->expectsOutput('✅ Laravel Analytics installation completed!')
            ->assertSuccessful();
    }

    /** @test */
    public function it_installs_assets_and_config_by_default_not_views()
    {
        $this->artisan('analytics:install')
            ->assertSuccessful();

        // Should publish config
        $this->assertFileExists($this->app->configPath('analytics.php'));

        // Should NOT publish views by default
        $this->assertDirectoryDoesNotExist($this->app->resourcePath('views/vendor/analytics'));
    }

    /** @test */
    public function it_can_install_only_assets()
    {
        $this->artisan('analytics:install --assets')
            ->expectsOutput('📦 Publishing analytics assets...')
            ->assertSuccessful();
    }

    /** @test */
    public function it_can_install_only_config()
    {
        $this->artisan('analytics:install --config')
            ->expectsOutput('⚙️  Publishing analytics configuration...')
            ->assertSuccessful();
    }

    /** @test */
    public function it_can_install_only_views()
    {
        $this->artisan('analytics:install --views')
            ->expectsOutput('🎨 Publishing analytics views...')
            ->assertSuccessful();
    }

    /** @test */
    public function it_can_force_overwrite_existing_files()
    {
        // First installation
        $this->artisan('analytics:install --config')->assertSuccessful();

        // Force overwrite
        $this->artisan('analytics:install --config --force')
            ->expectsOutput('⚙️  Publishing analytics configuration...')
            ->assertSuccessful();
    }

    /** @test */
    public function it_displays_post_install_instructions_for_full_install()
    {
        $this->artisan('analytics:install')
            ->expectsOutput('📋 Next Steps:')
            ->expectsOutput('1. Configure Google Analytics credentials in .env:')
            ->expectsOutput('   GOOGLE_ANALYTICS_PROPERTY_ID=your-ga4-property-id')
            ->expectsOutput('   GOOGLE_ANALYTICS_CREDENTIALS_JSON=service-account-credentials.json')
            ->assertSuccessful();
    }

    /** @test */
    public function it_does_not_display_post_install_instructions_for_partial_install()
    {
        $this->artisan('analytics:install --assets')
            ->doesntExpectOutput('📋 Next Steps:')
            ->assertSuccessful();
    }

    /** @test */
    public function it_can_publish_assets_via_vendor_publish()
    {
        $this->artisan('vendor:publish --tag=analytics-assets --force')
            ->assertSuccessful();
    }

    /** @test */
    public function it_can_publish_config_via_vendor_publish()
    {
        $this->artisan('vendor:publish --tag=analytics-config --force')
            ->assertSuccessful();
    }

    /** @test */
    public function it_can_publish_views_via_vendor_publish()
    {
        $this->artisan('vendor:publish --tag=analytics-views --force')
            ->assertSuccessful();
    }

    /** @test */
    public function it_can_publish_all_via_vendor_publish()
    {
        $this->artisan('vendor:publish --tag=analytics --force')
            ->assertSuccessful();
    }

    /** @test */
    public function it_creates_analytics_directory_during_full_install()
    {
        $analyticsPath = storage_path('app/analytics');

        // Ensure directory doesn't exist before test
        if (File::exists($analyticsPath)) {
            File::deleteDirectory($analyticsPath);
        }

        $this->assertDirectoryDoesNotExist($analyticsPath);

        $this->artisan('analytics:install')
            ->expectsOutput('📁 Created analytics directory: '.$analyticsPath)
            ->assertSuccessful();

        $this->assertDirectoryExists($analyticsPath);

        // Clean up
        File::deleteDirectory($analyticsPath);
    }

    /** @test */
    public function it_does_not_create_analytics_directory_for_partial_install()
    {
        $analyticsPath = storage_path('app/analytics');

        // Ensure directory doesn't exist before test
        if (File::exists($analyticsPath)) {
            File::deleteDirectory($analyticsPath);
        }

        $this->artisan('analytics:install --assets')
            ->doesntExpectOutput('📁 Created analytics directory:')
            ->assertSuccessful();
    }

    private function cleanupPublishedFiles(): void
    {
        $paths = [
            $this->app->configPath('analytics.php'),
            $this->app->resourcePath('views/vendor/analytics'),
            $this->app->publicPath('vendor/analytics'),
        ];

        foreach ($paths as $path) {
            if (File::exists($path)) {
                File::deleteDirectory($path);
            } elseif (File::isFile($path)) {
                File::delete($path);
            }
        }
    }
}
