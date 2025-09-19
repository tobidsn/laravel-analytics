<?php

declare(strict_types=1);

namespace Tobidsn\LaravelAnalytics\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Tobidsn\LaravelAnalytics\Http\Requests\AnalyticsRequest;
use Tobidsn\LaravelAnalytics\Services\AnalyticsStrategyFactory;
use Tobidsn\LaravelAnalytics\Services\DateRangeCalculator;

class TrafficTableController extends Controller
{
    public function __construct(
        private readonly AnalyticsStrategyFactory $strategyFactory,
        private readonly DateRangeCalculator $dateRangeCalculator
    ) {}

    public function __invoke(AnalyticsRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $dateRange = $this->dateRangeCalculator->calculateFromPreset(
            $validated['preset'] ?? null,
            $validated['start_date'] ?? null,
            $validated['end_date'] ?? null,
            $validated['days'] ?? 30
        );

        $strategy = $this->strategyFactory->create('traffic_table');
        $data = $strategy->handle($dateRange, [
            'preset' => $validated['preset'] ?? null,
            'page' => $validated['page'] ?? 1,
            'per_page' => $validated['per_page'] ?? 10,
            'sort_by' => $validated['sort_by'] ?? 'sessions',
            'sort_direction' => $validated['sort_direction'] ?? 'desc',
        ]);

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'preset' => $validated['preset'] ?? null,
                'start_date' => $dateRange->startDate->format('Y-m-d'),
                'end_date' => $dateRange->endDate->format('Y-m-d'),
                'total_days' => $dateRange->getDays(),
                'current_page' => $validated['page'] ?? 1,
                'per_page' => $validated['per_page'] ?? 10,
            ],
        ]);
    }
}
