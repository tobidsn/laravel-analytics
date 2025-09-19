<?php

namespace Tobidsn\LaravelAnalytics;

use Tobidsn\LaravelAnalytics\Services\AnalyticsService;

class Analytics
{
    protected AnalyticsService $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Get comprehensive summary with KPI metrics
     */
    public function getSummary(int $days = 30, ?string $startDate = null, ?string $endDate = null, ?string $preset = null): array
    {
        return $this->analyticsService->fetchComprehensiveSummary($days, $startDate, $endDate, $preset);
    }

    /**
     * Get daily performance data for charts
     */
    public function getDailyPerformance(int $days = 30, ?string $startDate = null, ?string $endDate = null): array
    {
        return $this->analyticsService->fetchDailyPerformanceData($days, $startDate, $endDate);
    }

    /**
     * Get traffic sources data
     */
    public function getTrafficSources(int $days = 30, int $limit = 10, ?string $startDate = null, ?string $endDate = null): array
    {
        return $this->analyticsService->fetchTrafficSources($days, $limit, $startDate, $endDate);
    }

    /**
     * Get traffic acquisition data (new vs returning)
     */
    public function getTrafficAcquisition(int $days = 30, ?string $startDate = null, ?string $endDate = null): array
    {
        return $this->analyticsService->fetchTrafficAcquisitionData($days, $startDate, $endDate);
    }

    /**
     * Get landing pages data
     */
    public function getLandingPages(int $days = 30, int $limit = 10, ?string $startDate = null, ?string $endDate = null): array
    {
        return $this->analyticsService->fetchLandingPages($days, $limit, $startDate, $endDate);
    }

    /**
     * Get top content data
     */
    public function getTopContent(int $days = 30, int $limit = 10): array
    {
        return $this->analyticsService->fetchTopContent($days, $limit);
    }

    /**
     * Check if analytics is properly configured
     */
    public function isConfigured(): bool
    {
        return AnalyticsService::isConfigured();
    }

    /**
     * Test the connection to Google Analytics
     */
    public function testConnection(): bool
    {
        try {
            // Try to make a simple request to test the connection
            $this->getSummary(1);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Clear analytics cache
     */
    public function clearCache(): void
    {
        // This would clear any cached analytics data
        // Implementation depends on the cache strategy
        cache()->tags(['analytics'])->flush();
    }

    /**
     * Get the GA4 property ID
     */
    public function getPropertyId(): string
    {
        return $this->analyticsService->getPropertyId();
    }

    // Convenient fluent methods for easy usage

    /**
     * Start a query for visitors data
     */
    public function visitors(): self
    {
        return $this;
    }

    /**
     * Start a query for page views data
     */
    public function pageViews(): self
    {
        return $this;
    }

    /**
     * Start a query for pages data
     */
    public function pages(): self
    {
        return $this;
    }

    /**
     * Start a query for sources data
     */
    public function sources(): self
    {
        return $this;
    }

    /**
     * Get data for the last N days
     */
    public function lastDays(int $days): array
    {
        return $this->getSummary($days);
    }

    /**
     * Get data for a specific date range
     */
    public function startDate(string $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * Set end date for the query
     */
    public function endDate(string $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Get most visited pages
     */
    public function mostVisited(int $limit = 10): array
    {
        return $this->getLandingPages(30, $limit);
    }

    /**
     * Get traffic sources breakdown
     */
    public function breakdown(): array
    {
        return $this->getTrafficSources();
    }

    /**
     * Execute the query and get results
     */
    public function get(): array
    {
        return $this->getSummary();
    }
}
