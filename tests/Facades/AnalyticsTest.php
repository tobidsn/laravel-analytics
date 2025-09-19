<?php

use Tobidsn\LaravelAnalytics\Analytics as AnalyticsService;
use Tobidsn\LaravelAnalytics\Facades\Analytics;

describe('Analytics Facade', function () {

    it('resolves to the correct service class', function () {
        $facade = Analytics::getFacadeRoot();

        expect($facade)->toBeInstanceOf(AnalyticsService::class);
    });

    it('can call KPI metrics method', function () {
        $mockAnalytics = mock(AnalyticsService::class);
        $mockAnalytics->shouldReceive('getSummary')
            ->with(7, null, null, null)
            ->once()
            ->andReturn(['total_users' => 1000]);

        Analytics::swap($mockAnalytics);

        $result = Analytics::getSummary(7, null, null, null);

        expect($result)->toEqual(['total_users' => 1000]);
    });

    it('can call daily chart method', function () {
        $mockAnalytics = mock(AnalyticsService::class);
        $mockAnalytics->shouldReceive('getDailyPerformance')
            ->with(30, null, null)
            ->once()
            ->andReturn([
                ['date' => '2024-01-01', 'users' => 100],
            ]);

        Analytics::swap($mockAnalytics);

        $result = Analytics::getDailyPerformance(30, null, null);

        expect($result)->toBeArray();
        expect($result[0])->toHaveKey('date');
        expect($result[0])->toHaveKey('users');
    });

    it('can call traffic chart method', function () {
        $mockAnalytics = mock(AnalyticsService::class);
        $mockAnalytics->shouldReceive('getTrafficSources')
            ->with(30, 10, null, null)
            ->once()
            ->andReturn([
                ['source' => 'google', 'sessions' => 500],
            ]);

        Analytics::swap($mockAnalytics);

        $result = Analytics::getTrafficSources(30, 10, null, null);

        expect($result)->toBeArray();
        expect($result[0])->toHaveKey('source');
        expect($result[0])->toHaveKey('sessions');
    });

    it('can test connection', function () {
        $mockService = mock(AnalyticsService::class);
        $mockService->shouldReceive('testConnection')
            ->once()
            ->andReturn(true);

        Analytics::swap($mockService);

        $result = Analytics::testConnection();

        expect($result)->toBe(true);
    });

    it('can check if configured', function () {
        $mockService = mock(AnalyticsService::class);
        $mockService->shouldReceive('isConfigured')
            ->once()
            ->andReturn(true);

        Analytics::swap($mockService);

        $result = Analytics::isConfigured();

        expect($result)->toBe(true);
    });

    it('can clear cache', function () {
        $mockService = mock(AnalyticsService::class);
        $mockService->shouldReceive('clearCache')
            ->once()
            ->andReturnNull();

        Analytics::swap($mockService);

        Analytics::clearCache();

        // If we get here without exception, the call was successful
        expect(true)->toBe(true);
    });
});
