<?php

namespace Tobidsn\LaravelAnalytics\Services\Strategies;

use Exception;
use Tobidsn\LaravelAnalytics\DataTransferObjects\DateRangeDto;
use Tobidsn\LaravelAnalytics\Services\AnalyticsCacheService;
use Tobidsn\LaravelAnalytics\Services\AnalyticsService;

final class LandingPagesStrategy extends BaseAnalyticsStrategy
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

        $cacheKey = $this->cacheService->generateKey('landing_pages', [
            'start_date' => $dateRange->startDate->format('Y-m-d'),
            'end_date' => $dateRange->endDate->format('Y-m-d'),
            'limit' => $limit,
            'page' => $page,
        ]);

        return $this->cacheService->remember($cacheKey, function () use ($dateRange, $limit, $page) {
            try {
                $fetchLimit = $limit * $page + 50;

                $landingPages = $this->analyticsService->fetchLandingPages(
                    $dateRange->getDays(),
                    $fetchLimit,
                    $dateRange->startDate->format('Y-m-d'),
                    $dateRange->endDate->format('Y-m-d')
                );

                $totals = $this->calculateTotals($landingPages);
                $paginationData = $this->paginateResults($landingPages, $page, $limit);

                return [
                    'landing_pages' => $paginationData['data'],
                    'pagination' => $paginationData['pagination'],
                    'totals' => $totals,
                    'date_range' => $dateRange->toArray(),
                ];
            } catch (Exception $e) {
                return $this->handleException($e, 'landing pages data', [
                    'landing_pages' => [],
                    'pagination' => [
                        'current_page' => $page,
                        'per_page' => $limit,
                        'total' => 0,
                        'last_page' => 1,
                    ],
                    'totals' => [
                        'sessions' => 0,
                        'new_users' => 0,
                        'total_users' => 0,
                        'bounce_rate' => 0,
                        'percentage' => 0,
                    ],
                    'date_range' => $dateRange->toArray(),
                ]);
            }
        }, 1800);
    }

    private function calculateTotals(array $landingPages): array
    {
        if (empty($landingPages)) {
            return [
                'sessions' => 0,
                'new_users' => 0,
                'total_users' => 0,
                'bounce_rate' => 0,
                'percentage' => 100,
            ];
        }

        return [
            'sessions' => array_sum(array_column($landingPages, 'sessions')),
            'new_users' => array_sum(array_column($landingPages, 'new_users')),
            'total_users' => array_sum(array_column($landingPages, 'total_users')),
            'bounce_rate' => round(
                array_sum(array_column($landingPages, 'bounce_rate')) / count($landingPages),
                2
            ),
            'percentage' => array_sum(array_column($landingPages, 'percentage')),
        ];
    }

    private function paginateResults(array $data, int $page, int $perPage): array
    {
        $total = count($data);
        $lastPage = (int) ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;
        $paginatedData = array_slice($data, $offset, $perPage);

        foreach ($paginatedData as $index => &$item) {
            $item['rank'] = $offset + $index + 1;
        }

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
