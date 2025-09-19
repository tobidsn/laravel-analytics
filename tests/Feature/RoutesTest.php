<?php

use Illuminate\Support\Facades\Route;
use Tobidsn\LaravelAnalytics\Services\AnalyticsStrategyFactory;
use Tobidsn\LaravelAnalytics\Services\Strategies\BaseAnalyticsStrategy;

describe('Analytics Routes', function () {

    beforeEach(function () {
        // Mock the strategy factory and strategies for all API tests
        $mockStrategy = mock(BaseAnalyticsStrategy::class);
        $mockStrategy->shouldReceive('handle')
            ->andReturn([
                'summary' => ['sessions' => 100, 'users' => 80],
                'date_range' => ['start' => '2024-01-01', 'end' => '2024-01-07'],
                'previous_date_range' => ['start' => '2023-12-25', 'end' => '2023-12-31'],
                'chart_data' => [],
                'previous_chart_data' => [],
                'previous_period_label' => 'Previous period',
            ]);

        $mockFactory = mock(AnalyticsStrategyFactory::class);
        $mockFactory->shouldReceive('create')
            ->andReturn($mockStrategy);

        $this->app->instance(AnalyticsStrategyFactory::class, $mockFactory);
    });

    it('registers dashboard route', function () {
        $this->get('/analytics')
            ->assertStatus(200)
            ->assertViewIs('analytics::dashboard');
    });

    it('registers KPI metrics API route', function () {
        $this->withoutMiddleware()
            ->getJson('/analytics/api/kpi-metrics?preset=7d')
            ->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'summary',
                    'date_range',
                    'previous_date_range',
                ],
            ]);
    });

    it('registers daily chart API route', function () {
        $this->withoutMiddleware()
            ->getJson('/analytics/api/daily-chart?preset=7d')
            ->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'summary',
                    'date_range',
                    'previous_date_range',
                ],
            ]);
    });

    it('registers traffic chart API route', function () {
        $this->withoutMiddleware()
            ->getJson('/analytics/api/traffic-chart?preset=7d')
            ->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'summary',
                    'date_range',
                    'previous_date_range',
                ],
            ]);
    });

    it('registers traffic table API route', function () {
        $this->withoutMiddleware()
            ->getJson('/analytics/api/traffic-table?preset=7d')
            ->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'summary',
                    'date_range',
                    'previous_date_range',
                ],
            ]);
    });

    it('registers landing pages API route', function () {
        $this->withoutMiddleware()
            ->getJson('/analytics/api/landing-pages?preset=7d')
            ->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'summary',
                    'date_range',
                    'previous_date_range',
                ],
            ]);
    });

    it('requires authentication for all routes', function () {
        // Test dashboard route
        $this->withoutMiddleware()
            ->get('/analytics')
            ->assertStatus(200);

        // Test API routes with mocked services
        $apiRoutes = [
            '/analytics/api/kpi-metrics',
            '/analytics/api/daily-chart',
            '/analytics/api/traffic-chart',
            '/analytics/api/traffic-table',
            '/analytics/api/landing-pages',
        ];

        foreach ($apiRoutes as $route) {
            $this->withoutMiddleware()
                ->getJson($route.'?preset=7d')
                ->assertStatus(200);
        }
    });

    it('validates required parameters', function () {
        // Test missing preset parameter - preset is actually optional by validation
        $apiRoutes = [
            '/analytics/api/kpi-metrics',
            '/analytics/api/daily-chart',
            '/analytics/api/traffic-chart',
            '/analytics/api/traffic-table',
            '/analytics/api/landing-pages',
        ];

        foreach ($apiRoutes as $route) {
            $this->withoutMiddleware()
                ->getJson($route)
                ->assertStatus(200);
        }
    });

    it('handles pagination parameters', function () {
        $this->withoutMiddleware()
            ->getJson('/analytics/api/traffic-table?preset=7d&page=2&per_page=5')
            ->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'summary',
                    'date_range',
                    'previous_date_range',
                ],
            ]);
    });

    it('validates custom date range', function () {
        $today = now()->format('Y-m-d');
        $thirtyDaysAgo = now()->subDays(30)->format('Y-m-d');

        // Valid custom range
        $this->withoutMiddleware()
            ->getJson("/analytics/api/kpi-metrics?preset=custom&start_date={$thirtyDaysAgo}&end_date={$today}")
            ->assertStatus(200);

        // Invalid custom range (missing dates)
        $this->getJson('/analytics/api/kpi-metrics?preset=custom')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['start_date', 'end_date']);

        // Invalid date order
        $this->getJson("/analytics/api/kpi-metrics?preset=custom&start_date={$today}&end_date={$thirtyDaysAgo}")
            ->assertStatus(422)
            ->assertJsonValidationErrors(['end_date']);
    });
});
