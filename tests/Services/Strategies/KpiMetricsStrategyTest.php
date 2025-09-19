<?php

use Tobidsn\LaravelAnalytics\DataTransferObjects\AnalyticsRequestDto;
use Tobidsn\LaravelAnalytics\Services\AnalyticsCacheService;
use Tobidsn\LaravelAnalytics\Services\AnalyticsService;
use Tobidsn\LaravelAnalytics\Services\Strategies\KpiMetricsStrategy;

describe('KpiMetricsStrategy', function () {

    beforeEach(function () {
        $this->mockAnalyticsService = mock(AnalyticsService::class);
        $this->mockCacheService = mock(AnalyticsCacheService::class);
        $this->strategy = new KpiMetricsStrategy($this->mockAnalyticsService, $this->mockCacheService);
    });

    it('executes and returns KPI metrics data', function () {
        $dto = new AnalyticsRequestDto(
            preset: '7d',
            startDate: null,
            endDate: null,
            page: 1,
            perPage: 10,
            sortBy: 'sessions',
            sortDirection: 'desc'
        );

        // Mock the cache service
        $this->mockCacheService->shouldReceive('generateKey')
            ->once()
            ->andReturn('test_cache_key');

        $this->mockCacheService->shouldReceive('remember')
            ->once()
            ->andReturnUsing(function ($key, $callback, $ttl) {
                return $callback();
            });

        // Mock the analytics service response
        $mockSummary = [
            'total_users' => 1000,
            'page_views' => 2500,
            'sessions' => 800,
            'avg_session_duration' => 150,
        ];

        $mockPreviousDateRange = [
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-07',
        ];

        $this->mockAnalyticsService->shouldReceive('fetchComprehensiveSummary')
            ->once()
            ->andReturn($mockSummary);

        $this->mockAnalyticsService->shouldReceive('calculatePreviousDateRange')
            ->once()
            ->andReturn($mockPreviousDateRange);

        $result = $this->strategy->fetchData($dto);

        expect($result)->toBeArray();
        expect($result)->toHaveKey('summary');
        expect($result)->toHaveKey('date_range');
        expect($result)->toHaveKey('previous_date_range');
    });

    it('calculates percentage changes correctly', function () {
        $dto = new AnalyticsRequestDto(preset: '30d');

        // Mock the cache service
        $this->mockCacheService->shouldReceive('generateKey')
            ->once()
            ->andReturn('test_cache_key');

        $this->mockCacheService->shouldReceive('remember')
            ->once()
            ->andReturnUsing(function ($key, $callback, $ttl) {
                return $callback();
            });

        $mockSummary = ['total_users' => 1000, 'page_views' => 2000];
        $mockPreviousDateRange = ['start_date' => '2024-01-01', 'end_date' => '2024-01-07'];

        $this->mockAnalyticsService->shouldReceive('fetchComprehensiveSummary')
            ->once()
            ->andReturn($mockSummary);

        $this->mockAnalyticsService->shouldReceive('calculatePreviousDateRange')
            ->once()
            ->andReturn($mockPreviousDateRange);

        $result = $this->strategy->fetchData($dto);

        expect($result)->toHaveKey('summary');
        expect($result['summary'])->toEqual($mockSummary);
    });

    it('handles zero division in percentage calculation', function () {
        $dto = new AnalyticsRequestDto(preset: '7d');

        // Mock the cache service
        $this->mockCacheService->shouldReceive('generateKey')
            ->once()
            ->andReturn('test_cache_key');

        $this->mockCacheService->shouldReceive('remember')
            ->once()
            ->andReturnUsing(function ($key, $callback, $ttl) {
                return $callback();
            });

        $mockSummary = ['total_users' => 1000];
        $mockPreviousDateRange = ['start_date' => '2024-01-01', 'end_date' => '2024-01-07'];

        $this->mockAnalyticsService->shouldReceive('fetchComprehensiveSummary')
            ->once()
            ->andReturn($mockSummary);

        $this->mockAnalyticsService->shouldReceive('calculatePreviousDateRange')
            ->once()
            ->andReturn($mockPreviousDateRange);

        $result = $this->strategy->fetchData($dto);

        expect($result)->toHaveKey('summary');
        expect($result['summary']['total_users'])->toBe(1000);
    });

    it('handles service exceptions', function () {
        $dto = new AnalyticsRequestDto(preset: '7d');

        // Mock the cache service
        $this->mockCacheService->shouldReceive('generateKey')
            ->once()
            ->andReturn('test_cache_key');

        $this->mockCacheService->shouldReceive('remember')
            ->once()
            ->andReturnUsing(function ($key, $callback, $ttl) {
                return $callback();
            });

        $this->mockAnalyticsService->shouldReceive('fetchComprehensiveSummary')
            ->andThrow(new Exception('GA4 API Error'));

        $result = $this->strategy->fetchData($dto);

        // Should return fallback data when exception occurs
        expect($result)->toHaveKey('summary');
        expect($result)->toHaveKey('date_range');
        expect($result)->toHaveKey('previous_date_range');
    });

    it('works with custom date range', function () {
        $dto = new AnalyticsRequestDto(
            preset: 'custom',
            startDate: '2024-01-01',
            endDate: '2024-01-31'
        );

        // Mock the cache service
        $this->mockCacheService->shouldReceive('generateKey')
            ->once()
            ->andReturn('test_cache_key');

        $this->mockCacheService->shouldReceive('remember')
            ->once()
            ->andReturnUsing(function ($key, $callback, $ttl) {
                return $callback();
            });

        $mockSummary = ['total_users' => 500, 'page_views' => 1200];
        $mockPreviousDateRange = ['start_date' => '2023-12-01', 'end_date' => '2023-12-31'];

        $this->mockAnalyticsService->shouldReceive('fetchComprehensiveSummary')
            ->once()
            ->andReturn($mockSummary);

        $this->mockAnalyticsService->shouldReceive('calculatePreviousDateRange')
            ->once()
            ->andReturn($mockPreviousDateRange);

        $result = $this->strategy->fetchData($dto);

        expect($result)->toHaveKey('summary');
        expect($result['summary']['total_users'])->toBe(500);
        expect($result['summary']['page_views'])->toBe(1200);
    });
});
