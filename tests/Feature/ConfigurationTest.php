<?php

namespace Tobidsn\LaravelAnalytics\Tests\Feature;

use Tobidsn\LaravelAnalytics\Tests\TestCase;

class ConfigurationTest extends TestCase
{
    /** @test */
    public function it_resolves_service_account_credentials_path_correctly()
    {
        // Test with default filename
        putenv('GOOGLE_ANALYTICS_CREDENTIALS_JSON=service-account-credentials.json');

        $expectedPath = storage_path('app/analytics/service-account-credentials.json');
        $actualPath = storage_path('app/analytics/'.env('GOOGLE_ANALYTICS_CREDENTIALS_JSON', 'service-account-credentials.json'));

        $this->assertEquals($expectedPath, $actualPath);

        // Clean up
        putenv('GOOGLE_ANALYTICS_CREDENTIALS_JSON');
    }

    /** @test */
    public function it_resolves_service_account_credentials_path_with_custom_filename()
    {
        // Test with custom filename
        putenv('GOOGLE_ANALYTICS_CREDENTIALS_JSON=my-custom-credentials.json');

        $expectedPath = storage_path('app/analytics/my-custom-credentials.json');
        $actualPath = storage_path('app/analytics/'.env('GOOGLE_ANALYTICS_CREDENTIALS_JSON', 'service-account-credentials.json'));

        $this->assertEquals($expectedPath, $actualPath);

        // Clean up
        putenv('GOOGLE_ANALYTICS_CREDENTIALS_JSON');
    }

    /** @test */
    public function it_uses_default_filename_when_env_not_set()
    {
        // Clear the env variable
        putenv('GOOGLE_ANALYTICS_CREDENTIALS_JSON');

        $expectedPath = storage_path('app/analytics/service-account-credentials.json');
        $actualPath = storage_path('app/analytics/'.env('GOOGLE_ANALYTICS_CREDENTIALS_JSON', 'service-account-credentials.json'));

        $this->assertEquals($expectedPath, $actualPath);
    }

    /** @test */
    public function it_maintains_backwards_compatibility_in_tests()
    {
        // Verify that our test configuration override still works
        $testPath = __DIR__.'/../fixtures/test-credentials.json';

        config()->set('analytics.service_account_credentials_json', $testPath);

        $actualPath = config('analytics.service_account_credentials_json');

        $this->assertEquals($testPath, $actualPath);
        $this->assertFileExists($actualPath);
    }
}
