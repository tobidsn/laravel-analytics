<?php

namespace Tobidsn\LaravelAnalytics\Services\Strategies;

use Exception;
use Tobidsn\LaravelAnalytics\DataTransferObjects\DateRangeDto;
use Tobidsn\LaravelAnalytics\Services\AnalyticsCacheService;
use Tobidsn\LaravelAnalytics\Services\AnalyticsService;

final class TrafficChartStrategy extends BaseAnalyticsStrategy
{
    public function __construct(
        protected AnalyticsService $analyticsService,
        private AnalyticsCacheService $cacheService
    ) {
        parent::__construct($analyticsService);
    }

    public function handle(DateRangeDto $dateRange, array $params = []): array
    {
        $cacheKey = $this->cacheService->generateKey('traffic_chart', [
            'start_date' => $dateRange->startDate->format('Y-m-d'),
            'end_date' => $dateRange->endDate->format('Y-m-d'),
        ]);

        return $this->cacheService->remember($cacheKey, function () use ($dateRange) {
            try {
                $trafficAcquisition = $this->analyticsService->fetchTrafficAcquisitionData(
                    $dateRange->getDays(),
                    $dateRange->startDate->format('Y-m-d'),
                    $dateRange->endDate->format('Y-m-d')
                );

                return [
                    'traffic_acquisition' => $trafficAcquisition,
                    'date_range' => $dateRange->toArray(),
                ];
            } catch (Exception $e) {
                return $this->handleException($e, 'traffic acquisition data', [
                    'traffic_acquisition' => [
                        'new_users_percentage' => 75,
                        'returning_users_percentage' => 25,
                    ],
                    'date_range' => $dateRange->toArray(),
                ]);
            }
        }, 1800); // Cache for 30 minutes
    }
}
