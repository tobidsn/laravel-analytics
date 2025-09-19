<?php

namespace Tobidsn\LaravelAnalytics\Services\Strategies;

use Illuminate\Support\Facades\Log;
use Tobidsn\LaravelAnalytics\DataTransferObjects\AnalyticsRequestDto;
use Tobidsn\LaravelAnalytics\DataTransferObjects\DateRangeDto;
use Tobidsn\LaravelAnalytics\Services\AnalyticsService;

abstract class BaseAnalyticsStrategy
{
    public function __construct(
        protected AnalyticsService $analyticsService
    ) {}

    abstract public function handle(DateRangeDto $dateRange, array $params = []): array;

    public function fetchData(AnalyticsRequestDto $dto): array
    {
        $dateRange = $dto->getDateRange();

        // Convert DTO to array params for the handle method
        $params = [
            'page' => $dto->page,
            'per_page' => $dto->perPage,
            'sort_by' => $dto->sortBy,
            'sort_direction' => $dto->sortDirection,
            'filters' => $dto->filters,
            'refresh_cache' => $dto->refreshCache,
            'preset' => $dto->preset,
        ];

        return $this->handle($dateRange, $params);
    }

    protected function handleException(\Exception $e, string $context, array $fallbackData = []): array
    {
        Log::error("Failed to fetch {$context}", [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        return $fallbackData;
    }
}
