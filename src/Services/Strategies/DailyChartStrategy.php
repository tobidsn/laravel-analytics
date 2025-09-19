<?php

namespace Tobidsn\LaravelAnalytics\Services\Strategies;

use Exception;
use Tobidsn\LaravelAnalytics\DataTransferObjects\DateRangeDto;
use Tobidsn\LaravelAnalytics\Services\AnalyticsCacheService;
use Tobidsn\LaravelAnalytics\Services\AnalyticsService;

final class DailyChartStrategy extends BaseAnalyticsStrategy
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

        $cacheKey = $this->cacheService->generateKey('daily_chart', [
            'start_date' => $dateRange->startDate->format('Y-m-d'),
            'end_date' => $dateRange->endDate->format('Y-m-d'),
            'preset' => $preset,
        ]);

        return $this->cacheService->remember($cacheKey, function () use ($dateRange, $preset) {
            try {
                $chartData = $this->analyticsService->fetchDailyPerformanceData(
                    $dateRange->getDays(),
                    $dateRange->startDate->format('Y-m-d'),
                    $dateRange->endDate->format('Y-m-d')
                );

                $chartData = $this->filterAndFillData($chartData, $dateRange, $preset);
                $previousChartData = $this->generatePreviousPeriodData($dateRange, $preset);

                return [
                    'chart_data' => $chartData,
                    'previous_chart_data' => $previousChartData,
                    'previous_period_label' => $this->generatePreviousPeriodLabel($dateRange, $preset),
                ];
            } catch (Exception $e) {
                return $this->handleException($e, 'daily chart data', [
                    'chart_data' => [],
                    'previous_chart_data' => [],
                    'previous_period_label' => '',
                ]);
            }
        }, 1800); // Cache for 30 minutes
    }

    private function filterAndFillData(array $chartData, DateRangeDto $dateRange, ?string $preset): array
    {
        if ($preset !== 'custom') {
            $chartData = array_filter($chartData, function ($item) use ($dateRange) {
                $itemDate = \Carbon\Carbon::parse($item['date']);

                return $itemDate->gte($dateRange->startDate) && $itemDate->lte($dateRange->endDate);
            });
        } else {
            $chartData = $this->fillMissingDates($chartData, $dateRange);
        }

        usort($chartData, fn ($a, $b) => strcmp($a['date'], $b['date']));

        if ($preset === 'ytd') {
            $chartData = $this->fillFutureDatesForYtd($chartData, $dateRange);
        }

        return $chartData;
    }

    private function fillMissingDates(array $chartData, DateRangeDto $dateRange): array
    {
        $filledData = [];
        $currentDate = $dateRange->startDate->copy();

        while ($currentDate->lte($dateRange->endDate)) {
            $dateString = $currentDate->format('Y-m-d');

            $existingData = collect($chartData)->firstWhere('date', $dateString);

            $filledData[] = $existingData ?: [
                'date' => $dateString,
                'users' => 0,
                'sessions' => 0,
                'page_views' => 0,
            ];

            $currentDate->addDay();
        }

        return $filledData;
    }

    private function fillFutureDatesForYtd(array $chartData, DateRangeDto $dateRange): array
    {
        if (empty($chartData)) {
            return $chartData;
        }

        $lastObject = end($chartData);
        $lastDate = \Carbon\Carbon::parse($lastObject['date'])->addDay();

        while ($lastDate->lte($dateRange->endDate)) {
            $chartData[] = [
                'date' => $lastDate->format('Y-m-d'),
                'users' => 0,
                'sessions' => 0,
                'page_views' => 0,
            ];
            $lastDate->addDay();
        }

        return $chartData;
    }

    private function generatePreviousPeriodData(DateRangeDto $dateRange, ?string $preset): array
    {
        try {
            $previousRange = app(\Tobidsn\LaravelAnalytics\Services\DateRangeCalculator::class)
                ->calculatePreviousRange($dateRange, $preset);

            $previousChartData = $this->analyticsService->fetchDailyPerformanceData(
                $previousRange->getDays(),
                $previousRange->startDate->format('Y-m-d'),
                $previousRange->endDate->format('Y-m-d')
            );

            return $this->filterAndFillData($previousChartData, $previousRange, $preset);
        } catch (Exception $e) {
            return $this->handleException($e, 'previous period data', []);
        }
    }

    private function generatePreviousPeriodLabel(DateRangeDto $dateRange, ?string $preset): string
    {
        return app(\Tobidsn\LaravelAnalytics\Services\DateRangeCalculator::class)
            ->generatePreviousPeriodLabel($dateRange, $preset);
    }
}
