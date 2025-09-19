<?php

namespace Tobidsn\LaravelAnalytics\Services;

use InvalidArgumentException;
use Tobidsn\LaravelAnalytics\Services\Strategies\BaseAnalyticsStrategy;
use Tobidsn\LaravelAnalytics\Services\Strategies\DailyChartStrategy;
use Tobidsn\LaravelAnalytics\Services\Strategies\KpiMetricsStrategy;
use Tobidsn\LaravelAnalytics\Services\Strategies\LandingPagesStrategy;
use Tobidsn\LaravelAnalytics\Services\Strategies\TrafficChartStrategy;
use Tobidsn\LaravelAnalytics\Services\Strategies\TrafficTableStrategy;

class AnalyticsStrategyFactory
{
    public function __construct(
        private AnalyticsService $analyticsService,
        private AnalyticsCacheService $cacheService
    ) {}

    public function create(string $type): BaseAnalyticsStrategy
    {
        return match ($type) {
            'daily-chart', 'daily_chart' => new DailyChartStrategy($this->analyticsService, $this->cacheService),
            'kpi-metrics', 'kpi_metrics' => new KpiMetricsStrategy($this->analyticsService, $this->cacheService),
            'traffic-chart', 'traffic_chart' => new TrafficChartStrategy($this->analyticsService, $this->cacheService),
            'traffic-table', 'traffic_table' => new TrafficTableStrategy($this->analyticsService, $this->cacheService),
            'landing-pages', 'landing_pages' => new LandingPagesStrategy($this->analyticsService, $this->cacheService),
            default => throw new InvalidArgumentException("Unsupported analytics type: {$type}"),
        };
    }

    public function getSupportedTypes(): array
    {
        return ['daily-chart', 'kpi-metrics', 'traffic-chart', 'traffic-table', 'landing-pages'];
    }
}
