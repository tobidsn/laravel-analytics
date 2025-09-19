<?php

namespace Tobidsn\LaravelAnalytics\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class AnalyticsInstallCommand extends Command
{
    public $signature = 'analytics:install 
                         {--assets : Only publish assets}
                         {--config : Only publish configuration} 
                         {--views : Only publish views}
                         {--force : Force publish, overwriting existing files}';

    public $description = 'Install Laravel Analytics package assets, config, and views';

    public function handle(): int
    {
        $this->info('🚀 Installing Laravel Analytics Package...');
        $this->newLine();

        $assetsOnly = $this->option('assets');
        $configOnly = $this->option('config');
        $viewsOnly = $this->option('views');
        $force = $this->option('force');

        // If no specific option is provided, install assets and config (excluding views)
        $installDefault = ! $assetsOnly && ! $configOnly && ! $viewsOnly;

        if ($installDefault || $assetsOnly) {
            $this->publishAssets($force);
        }

        if ($installDefault || $configOnly) {
            $this->publishConfig($force);
        }

        if ($installDefault) {
            $this->createAnalyticsDirectory();
        }

        if ($viewsOnly) {
            $this->publishViews($force);
        }

        if ($installDefault) {
            $this->displayPostInstallInstructions();
        }

        $this->newLine();
        $this->info('✅ Laravel Analytics installation completed!');

        return self::SUCCESS;
    }

    protected function publishAssets(bool $force = false): void
    {
        $this->info('📦 Publishing analytics assets...');

        $params = ['--tag' => 'analytics-assets'];
        if ($force) {
            $params['--force'] = true;
        }

        $this->call('vendor:publish', $params);

        $assetsPath = public_path(config('analytics.assets_path', 'vendor/analytics'));

        if (File::exists($assetsPath)) {
            $this->line("   → Assets published to: {$assetsPath}");

            // List published files
            $files = File::allFiles($assetsPath);
            foreach ($files as $file) {
                $relativePath = str_replace($assetsPath.'/', '', $file->getPathname());
                $size = $this->formatBytes($file->getSize());
                $this->line("     ✓ {$relativePath} ({$size})");
            }
        } else {
            $this->warn('   → No assets were published');
        }
    }

    protected function publishConfig(bool $force = false): void
    {
        $this->info('⚙️  Publishing analytics configuration...');

        $params = ['--tag' => 'analytics-config'];
        if ($force) {
            $params['--force'] = true;
        }

        $this->call('vendor:publish', $params);

        $configPath = config_path('analytics.php');
        if (File::exists($configPath)) {
            $this->line("   → Configuration published to: {$configPath}");
        } else {
            $this->warn('   → Configuration was not published');
        }
    }

    protected function publishViews(bool $force = false): void
    {
        $this->info('🎨 Publishing analytics views...');

        $params = ['--tag' => 'analytics-views'];
        if ($force) {
            $params['--force'] = true;
        }

        $this->call('vendor:publish', $params);

        $viewsPath = resource_path('views/vendor/analytics');
        if (File::exists($viewsPath)) {
            $this->line("   → Views published to: {$viewsPath}");

            // List published view files
            $files = File::allFiles($viewsPath);
            foreach ($files as $file) {
                $relativePath = str_replace($viewsPath.'/', '', $file->getPathname());
                $this->line("     ✓ {$relativePath}");
            }
        } else {
            $this->warn('   → No views were published');
        }
    }

    protected function createAnalyticsDirectory(): void
    {
        $analyticsPath = storage_path('app/analytics');

        if (! File::exists($analyticsPath)) {
            File::makeDirectory($analyticsPath, 0755, true);
            $this->info('📁 Created analytics directory: '.$analyticsPath);
        } else {
            $this->line('📁 Analytics directory already exists: '.$analyticsPath);
        }
    }

    protected function displayPostInstallInstructions(): void
    {
        $this->newLine();
        $this->info('📋 Next Steps:');
        $this->newLine();

        $this->line('1. Configure Google Analytics credentials in .env:');
        $this->line('   GOOGLE_ANALYTICS_PROPERTY_ID=your-ga4-property-id');
        $this->line('   GOOGLE_ANALYTICS_CREDENTIALS_JSON=service-account-credentials.json');
        $this->newLine();

        $this->line('2. Place your Google Analytics service account JSON file at:');
        $this->line('   storage/app/analytics/service-account-credentials.json');
        $this->line('   (or use the filename you specified in GOOGLE_ANALYTICS_CREDENTIALS_JSON)');
        $this->newLine();

        $this->line('3. Optional: Customize asset path in .env:');
        $this->line('   ANALYTICS_ASSETS_PATH=vendor/analytics');
        $this->newLine();

        $this->line('4. Test your connection:');
        $this->line('   php artisan analytics:test-connection');
        $this->newLine();

        $this->line('5. Visit your analytics dashboard:');
        $this->line('   http://your-domain.com/analytics');
        $this->newLine();

        $this->comment('💡 Pro Tips:');
        $this->line('   • Default install: assets + configuration only');
        $this->line('   • Use --force flag to overwrite existing files');
        $this->line('   • Use --assets to only publish CSS/JS files');
        $this->line('   • Use --config to only publish configuration');
        $this->line('   • Use --views to publish view templates for customization');
        $this->newLine();

        $this->line('📚 Documentation: https://github.com/tobidsn/laravel-analytics');
    }

    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision).' '.$units[$i];
    }
}
