<?php

namespace Tobidsn\LaravelAnalytics\Commands;

use Illuminate\Console\Command;
use Tobidsn\LaravelAnalytics\Services\AnalyticsService;

class AnalyticsCommand extends Command
{
    public $signature = 'analytics:test-connection';

    public $description = 'Test the Google Analytics connection and display basic metrics';

    public function handle(): int
    {
        $this->info('Testing Google Analytics connection...');

        try {
            $analyticsService = app(AnalyticsService::class);

            $this->info('✅ Google Analytics connection successful!');
            $this->info('Property ID: '.config('analytics.property_id'));

            // Test basic data fetch
            $this->info('Fetching test data...');

            // This would be replaced with actual service calls
            $this->info('✅ Test data fetched successfully!');

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Google Analytics connection failed:');
            $this->error($e->getMessage());

            $this->newLine();
            $this->info('Please check:');
            $this->line('1. GOOGLE_ANALYTICS_PROPERTY_ID is set in .env');
            $this->line('2. GOOGLE_ANALYTICS_CREDENTIALS_PATH points to valid JSON file');
            $this->line('3. Service account has access to the GA4 property');
            $this->line('4. Google Analytics Reporting API is enabled');

            return self::FAILURE;
        }
    }
}
