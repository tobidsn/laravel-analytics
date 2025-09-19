<?php

namespace Tobidsn\LaravelAnalytics\DataTransferObjects;

use Tobidsn\LaravelAnalytics\Http\Requests\AnalyticsRequest;

final readonly class AnalyticsRequestDto
{
    public function __construct(
        public ?string $preset = null,
        public ?string $startDate = null,
        public ?string $endDate = null,
        public int $page = 1,
        public int $perPage = 10,
        public string $sortBy = 'sessions',
        public string $sortDirection = 'desc',
        public array $filters = [],
        public bool $refreshCache = false,
    ) {}

    public static function fromRequest(AnalyticsRequest $request): self
    {
        return new self(
            preset: $request->input('preset'),
            startDate: $request->input('start_date'),
            endDate: $request->input('end_date'),
            page: (int) $request->input('page', 1),
            perPage: (int) $request->input('per_page', 10),
            sortBy: $request->input('sort_by', 'sessions'),
            sortDirection: $request->input('sort_direction', 'desc'),
            filters: $request->input('filters', []),
            refreshCache: (bool) $request->input('refresh_cache', false),
        );
    }

    public function getDateRange(): DateRangeDto
    {
        return app(\Tobidsn\LaravelAnalytics\Services\DateRangeCalculator::class)
            ->calculateFromPreset($this->preset, $this->startDate, $this->endDate);
    }
}
