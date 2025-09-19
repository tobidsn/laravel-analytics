<?php

namespace Tobidsn\LaravelAnalytics\Services\Strategies;

use Exception;
use Tobidsn\LaravelAnalytics\DataTransferObjects\DateRangeDto;
use Tobidsn\LaravelAnalytics\Services\AnalyticsCacheService;
use Tobidsn\LaravelAnalytics\Services\AnalyticsService;

final class TrafficTableStrategy extends BaseAnalyticsStrategy
{
    public function __construct(
        protected AnalyticsService $analyticsService,
        private AnalyticsCacheService $cacheService
    ) {
        parent::__construct($analyticsService);
    }

    public function handle(DateRangeDto $dateRange, array $params = []): array
    {
        $limit = $params['limit'] ?? 10;
        $page = $params['page'] ?? 1;

        $cacheKey = $this->cacheService->generateKey('traffic_table', [
            'start_date' => $dateRange->startDate->format('Y-m-d'),
            'end_date' => $dateRange->endDate->format('Y-m-d'),
            'limit' => $limit,
            'page' => $page,
        ]);

        return $this->cacheService->remember($cacheKey, function () use ($dateRange, $limit, $page) {
            try {
                $trafficSources = $this->analyticsService->fetchTrafficSources(
                    $dateRange->getDays(),
                    1000, // Fetch more data for pagination
                    $dateRange->startDate->format('Y-m-d'),
                    $dateRange->endDate->format('Y-m-d')
                );

                $totals = $this->calculateTotals($trafficSources);
                $paginationData = $this->paginateResults($trafficSources, $page, $limit);

                return [
                    'traffic_sources' => $paginationData['data'],
                    'pagination' => $paginationData['pagination'],
                    'totals' => $totals,
                    'date_range' => $dateRange->toArray(),
                ];
            } catch (Exception $e) {
                return $this->handleException($e, 'traffic sources data', [
                    'traffic_sources' => [],
                    'pagination' => [
                        'current_page' => $page,
                        'per_page' => $limit,
                        'total' => 0,
                        'last_page' => 1,
                    ],
                    'totals' => [
                        'sessions' => 0,
                        'newUsers' => 0,
                        'totalUsers' => 0,
                        'bounceRate' => 0,
                    ],
                    'date_range' => $dateRange->toArray(),
                ]);
            }
        }, 1200); // Cache for 20 minutes (shorter due to pagination)
    }

    private function calculateTotals(array $trafficSources): array
    {
        if (empty($trafficSources)) {
            return [
                'sessions' => 0,
                'newUsers' => 0,
                'totalUsers' => 0,
                'bounceRate' => 0,
            ];
        }

        return [
            'sessions' => array_sum(array_column($trafficSources, 'sessions')),
            'newUsers' => array_sum(array_column($trafficSources, 'newUsers')),
            'totalUsers' => array_sum(array_column($trafficSources, 'totalUsers')),
            'bounceRate' => round(
                array_sum(array_column($trafficSources, 'bounceRate')) / count($trafficSources),
                2
            ),
        ];
    }

    private function paginateResults(array $data, int $page, int $perPage): array
    {
        $total = count($data);
        $lastPage = (int) ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;
        $paginatedData = array_slice($data, $offset, $perPage);

        return [
            'data' => $paginatedData,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => $lastPage,
            ],
        ];
    }
}
