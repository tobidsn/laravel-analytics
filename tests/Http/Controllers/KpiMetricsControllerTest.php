<?php

use Illuminate\Http\Request;
use Tobidsn\LaravelAnalytics\Http\Controllers\KpiMetricsController;
use Tobidsn\LaravelAnalytics\Http\Requests\AnalyticsRequest;
use Tobidsn\LaravelAnalytics\Services\AnalyticsStrategyFactory;
use Tobidsn\LaravelAnalytics\Services\DateRangeCalculator;
use Tobidsn\LaravelAnalytics\Services\Strategies\BaseAnalyticsStrategy;

describe('KpiMetricsController', function () {

    beforeEach(function () {
        $this->mockFactory = mock(AnalyticsStrategyFactory::class);
        $this->dateRangeCalculator = app(DateRangeCalculator::class);
        $this->controller = new KpiMetricsController($this->mockFactory, $this->dateRangeCalculator);
    });

    it('returns successful KPI metrics response', function () {
        // Mock request data
        $request = mock(AnalyticsRequest::class);
        $request->shouldReceive('validated')->andReturn(['preset' => '7d']);

        // Mock expected response data
        $expectedData = [
            'total_users' => 1250,
            'users_change' => 12.5,
            'page_views' => 3450,
            'page_views_change' => 8.3,
            'sessions' => 980,
            'sessions_change' => -2.1,
            'avg_session_duration' => 180,
            'session_duration_change' => 5.7,
        ];

        // Mock the strategy
        $mockStrategy = mock(BaseAnalyticsStrategy::class);
        $mockStrategy->shouldReceive('handle')
            ->once()
            ->andReturn($expectedData);

        $this->mockFactory->shouldReceive('create')
            ->with('kpi_metrics')
            ->once()
            ->andReturn($mockStrategy);

        // Execute controller method
        $response = $this->controller->__invoke($request);

        // Assert response structure
        expect($response->getStatusCode())->toBe(200);

        $responseData = $response->getData(true);
        expect($responseData)->toHaveKey('success', true);
        expect($responseData)->toHaveKey('data');
        expect($responseData['data'])->toEqual($expectedData);
    });

    it('handles service exceptions gracefully', function () {
        $request = mock(AnalyticsRequest::class);
        $request->shouldReceive('validated')->andReturn(['preset' => '7d']);

        $this->mockFactory->shouldReceive('create')
            ->with('kpi_metrics')
            ->andThrow(new Exception('Google Analytics API error'));

        expect(fn () => $this->controller->__invoke($request))
            ->toThrow(Exception::class, 'Google Analytics API error');
    });

    it('validates request parameters correctly', function () {
        // Test with invalid preset
        $this->getJson(route('analytics.api.kpi-metrics').'?preset=invalid')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['preset']);

        // Test with invalid date format
        $this->getJson(route('analytics.api.kpi-metrics').'?preset=custom&start_date=invalid-date&end_date=2024-01-01')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['start_date']);

        // Test with end date before start date
        $this->getJson(route('analytics.api.kpi-metrics').'?preset=custom&start_date=2024-01-15&end_date=2024-01-01')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['end_date']);
    });

    it('returns cached data when available', function () {
        $request = mock(AnalyticsRequest::class);
        $request->shouldReceive('validated')->andReturn(['preset' => '7d']);

        $expectedData = [
            'summary' => [
                'total_users' => 1000,
                'page_views' => 3000,
                'sessions' => 800,
                'avg_session_duration' => 150,
            ],
            'date_range' => ['start' => '2024-01-01', 'end' => '2024-01-07'],
            'previous_date_range' => ['start' => '2023-12-25', 'end' => '2023-12-31'],
        ];

        // Mock the strategy to return expected data format
        $mockStrategy = mock(BaseAnalyticsStrategy::class);
        $mockStrategy->shouldReceive('handle')
            ->once()
            ->andReturn($expectedData);

        $this->mockFactory->shouldReceive('create')
            ->with('kpi_metrics')
            ->once()
            ->andReturn($mockStrategy);

        $response = $this->controller->__invoke($request);

        expect($response->getStatusCode())->toBe(200);
        $responseData = $response->getData(true);
        expect($responseData['success'])->toBeTrue();
        expect($responseData['data'])->toEqual($expectedData);
    });
});
