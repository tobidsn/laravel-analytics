<?php

namespace Tobidsn\LaravelAnalytics\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Laravel Analytics Package Facade
 *
 * Provides a convenient interface for accessing Google Analytics GA4 data
 *
 * @method static array getKpiMetrics(string $period = '7', ?string $startDate = null, ?string $endDate = null)
 * @method static array getDailyChart(string $period = '7', ?string $startDate = null, ?string $endDate = null)
 * @method static array getTrafficChart(string $period = '7', ?string $startDate = null, ?string $endDate = null)
 * @method static array getTrafficTable(string $period = '7', ?string $startDate = null, ?string $endDate = null, int $page = 1, int $perPage = 10)
 * @method static array getLandingPages(string $period = '7', ?string $startDate = null, ?string $endDate = null, int $page = 1, int $perPage = 10)
 * @method static array getVisitorsAndPageViews(int $days = 30)
 * @method static array getTotalVisitorsAndPageViews(int $days = 30)
 * @method static array getMostVisitedPages(int $days = 30, int $limit = 10)
 * @method static array getTopReferrers(int $days = 30, int $limit = 10)
 * @method static array getUserTypes(int $days = 30)
 * @method static array getTopBrowsers(int $days = 30, int $limit = 10)
 * @method static bool isConfigured()
 * @method static bool testConnection()
 * @method static void clearCache()
 * @method static \Google\Analytics\Data\V1beta\BetaAnalyticsDataClient getClient()
 *
 * @see \Tobidsn\LaravelAnalytics\Analytics
 */
class Analytics extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return \Tobidsn\LaravelAnalytics\Analytics::class;
    }
}
