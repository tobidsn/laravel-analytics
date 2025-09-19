<?php

use Tobidsn\LaravelAnalytics\Http\Controllers\DailyChartController;
use Tobidsn\LaravelAnalytics\Http\Requests\AnalyticsRequest;
use Tobidsn\LaravelAnalytics\Services\AnalyticsStrategyFactory;
use Tobidsn\LaravelAnalytics\Services\DateRangeCalculator;
use Tobidsn\LaravelAnalytics\Services\Strategies\BaseAnalyticsStrategy;

describe('DailyChartController', function () {

    beforeEach(function () {
        $this->mockFactory = mock(AnalyticsStrategyFactory::class);
        $this->dateRangeCalculator = app(DateRangeCalculator::class);
        $this->controller = new DailyChartController($this->mockFactory, $this->dateRangeCalculator);
    });

    it('returns daily chart data successfully', function () {
        $request = mock(AnalyticsRequest::class);
        $request->shouldReceive('validated')->andReturn(['preset' => '7d']);

        $expectedData = [
            'chart_data' => [
                ['date' => '2024-01-01', 'users' => 100, 'page_views' => 250, 'sessions' => 80],
                ['date' => '2024-01-02', 'users' => 120, 'page_views' => 280, 'sessions' => 95],
                ['date' => '2024-01-03', 'users' => 95, 'page_views' => 220, 'sessions' => 75],
            ],
            'previous_chart_data' => [],
            'previous_period_label' => 'Previous 7 days',
        ];

        $mockStrategy = mock(BaseAnalyticsStrategy::class);
        $mockStrategy->shouldReceive('handle')->once()->andReturn($expectedData);

        $this->mockFactory->shouldReceive('create')
            ->with('daily_chart')
            ->once()
            ->andReturn($mockStrategy);

        $response = $this->controller->__invoke($request);

        expect($response->getStatusCode())->toBe(200);

        $responseData = $response->getData(true);
        expect($responseData)->toHaveKey('success', true);
        expect($responseData['data'])->toHaveKeys(['chart_data', 'previous_chart_data', 'previous_period_label']);
    });

    it('handles empty data gracefully', function () {
        $request = mock(AnalyticsRequest::class);
        $request->shouldReceive('validated')->andReturn(['preset' => '7d']);

        $expectedData = [
            'chart_data' => [],
            'previous_chart_data' => [],
            'previous_period_label' => 'Previous 7 days',
        ];

        $mockStrategy = mock(BaseAnalyticsStrategy::class);
        $mockStrategy->shouldReceive('handle')->once()->andReturn($expectedData);

        $this->mockFactory->shouldReceive('create')->andReturn($mockStrategy);

        $response = $this->controller->__invoke($request);

        $responseData = $response->getData(true);
        expect($responseData['success'])->toBe(true);
        expect($responseData['data'])->toHaveKeys(['chart_data', 'previous_chart_data', 'previous_period_label']);
    });

    it('processes custom date range correctly', function () {
        $request = mock(AnalyticsRequest::class);
        $request->shouldReceive('validated')->andReturn([
            'preset' => 'custom',
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-07',
        ]);

        $expectedData = [
            'chart_data' => [],
            'previous_chart_data' => [],
            'previous_period_label' => 'Previous period',
        ];

        $mockStrategy = mock(BaseAnalyticsStrategy::class);
        $mockStrategy->shouldReceive('handle')
            ->once()
            ->andReturn($expectedData);

        $this->mockFactory->shouldReceive('create')->andReturn($mockStrategy);

        $response = $this->controller->__invoke($request);

        expect($response->getStatusCode())->toBe(200);
    });
});
