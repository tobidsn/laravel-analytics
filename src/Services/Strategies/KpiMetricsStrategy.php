<?php

namespace Tobidsn\LaravelAnalytics\Services\Strategies;

use Exception;
use Tobidsn\LaravelAnalytics\DataTransferObjects\DateRangeDto;
use Tobidsn\LaravelAnalytics\Services\AnalyticsCacheService;
use Tobidsn\LaravelAnalytics\Services\AnalyticsService;

final class KpiMetricsStrategy extends BaseAnalyticsStrategy
{
    public function __construct(
        protected AnalyticsService $analyticsService,
        private AnalyticsCacheService $cacheService
    ) {
        parent::__construct($analyticsService);
    }

    public function handle(DateRangeDto $dateRange, array $params = []): array
    {
        $preset = $params['preset'] ?? null;

        $cacheKey = $this->cacheService->generateKey('kpi_metrics', [
            'start_date' => $dateRange->startDate->format('Y-m-d'),
            'end_date' => $dateRange->endDate->format('Y-m-d'),
            'preset' => $preset,
        ]);

        return $this->cacheService->remember($cacheKey, function () use ($dateRange, $preset) {
            try {
                $summary = $this->analyticsService->fetchComprehensiveSummary(
                    $dateRange->getDays(),
                    $dateRange->startDate->format('Y-m-d'),
                    $dateRange->endDate->format('Y-m-d'),
                    $preset
                );

                $previousDateRange = $this->analyticsService->calculatePreviousDateRange(
                    $dateRange->startDate->format('Y-m-d'),
                    $dateRange->endDate->format('Y-m-d'),
                    $preset
                );

                return [
                    'summary' => $summary,
                    'date_range' => $dateRange->toArray(),
                    'previous_date_range' => $previousDateRange,
                ];
            } catch (Exception $e) {
                return $this->handleException($e, 'KPI metrics', [
                    'summary' => [],
                    'date_range' => $dateRange->toArray(),
                    'previous_date_range' => [],
                ]);
            }
        }, 1800); // Cache for 30 minutes
    }
}
