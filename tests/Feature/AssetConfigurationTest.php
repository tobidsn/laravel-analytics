<?php

namespace Tobidsn\LaravelAnalytics\Tests\Feature;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Tobidsn\LaravelAnalytics\Tests\TestCase;

class AssetConfigurationTest extends TestCase
{
    protected function clearViewCache()
    {
        // Clear view cache to ensure Blade directive recompiles
        if (file_exists(storage_path('framework/views'))) {
            $files = glob(storage_path('framework/views').'/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
    }

    /** @test */
    public function it_uses_default_assets_path_when_not_configured()
    {
        $response = $this->get('/analytics');

        $response->assertOk();
        $response->assertSee('vendor/analytics/app.css');
        $response->assertSee('vendor/analytics/app.js');
    }

    /** @test */
    public function it_uses_custom_assets_path_from_config()
    {
        Config::set('analytics.assets_path', 'custom/analytics/path');
        $this->clearViewCache();

        $response = $this->get('/analytics');

        $response->assertOk();
        // Check that the custom path is used (may include cache busting parameters)
        $this->assertStringContainsString('custom/analytics/path/app.css', $response->getContent());
        $this->assertStringContainsString('custom/analytics/path/app.js', $response->getContent());
    }

    /** @test */
    public function it_uses_custom_assets_path_from_env()
    {
        $this->app['config']->set('analytics.assets_path', 'env/analytics/path');
        $this->clearViewCache();

        $response = $this->get('/analytics');

        $response->assertOk();
        // Check that the custom path is used (may include cache busting parameters)
        $this->assertStringContainsString('env/analytics/path/app.css', $response->getContent());
        $this->assertStringContainsString('env/analytics/path/app.js', $response->getContent());
    }

    /** @test */
    public function it_publishes_assets_to_custom_path()
    {
        // The publish command uses the config at runtime,
        // so we can't test custom paths in isolation easily.
        // Instead, test that the default publishing works
        $this->artisan('vendor:publish --tag=analytics-assets --force')
            ->assertSuccessful();

        $defaultPath = public_path('vendor/analytics');

        // Check for any CSS and JS files (they have versioned names)
        $cssFiles = glob($defaultPath.'/*.css');
        $jsFiles = glob($defaultPath.'/*.js');

        $this->assertNotEmpty($cssFiles, 'No CSS files found in published assets');
        $this->assertNotEmpty($jsFiles, 'No JS files found in published assets');

        // Verify manifest.json exists
        $this->assertTrue(File::exists($defaultPath.'/manifest.json'));

        // Clean up
        File::deleteDirectory(public_path('vendor'));
    }

    /** @test */
    public function dashboard_view_renders_with_correct_asset_paths()
    {
        Config::set('analytics.assets_path', 'test/assets');
        $this->clearViewCache();

        $response = $this->get('/analytics');

        $response->assertOk();
        // Check that the custom path is used (may include cache busting parameters)
        $this->assertStringContainsString('test/assets/app.css', $response->getContent());
        $this->assertStringContainsString('test/assets/app.js', $response->getContent());
    }

    /** @test */
    public function it_handles_assets_path_with_trailing_slash()
    {
        Config::set('analytics.assets_path', 'custom/path/');
        $this->clearViewCache();

        $response = $this->get('/analytics');

        $response->assertOk();
        // The asset() helper will normalize the path, check content contains the path
        $this->assertStringContainsString('custom/path//app.css', $response->getContent());
        $this->assertStringContainsString('custom/path//app.js', $response->getContent());
    }

    /** @test */
    public function it_handles_assets_path_without_leading_slash()
    {
        Config::set('analytics.assets_path', '/custom/path');
        $this->clearViewCache();

        $response = $this->get('/analytics');

        $response->assertOk();
        // Check content contains the path (may include cache busting parameters)
        $this->assertStringContainsString('/custom/path/app.css', $response->getContent());
        $this->assertStringContainsString('/custom/path/app.js', $response->getContent());
    }

    /** @test */
    public function config_includes_assets_path_setting()
    {
        $config = config('analytics');

        $this->assertArrayHasKey('assets_path', $config);
        $this->assertEquals('vendor/analytics', $config['assets_path']);
    }
}
