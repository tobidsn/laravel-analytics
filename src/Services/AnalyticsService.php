<?php

namespace Tobidsn\LaravelAnalytics\Services;

use Exception;
use Google\Analytics\Data\V1beta\Client\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\Metric;
use Google\Analytics\Data\V1beta\RunReportRequest;
use Illuminate\Support\Facades\Log;

class AnalyticsService
{
    private BetaAnalyticsDataClient $client;

    private string $propertyId;

    public function __construct(GoogleAnalyticsClientFactory $clientFactory)
    {
        $this->client = $clientFactory->create();
        $this->propertyId = $clientFactory->getPropertyId();
    }

    /**
     * Fetch top content analytics data
     */
    public function fetchTopContent(int $days = 30, int $limit = 10): array
    {
        try {
            $request = new RunReportRequest([
                'property' => $this->propertyId,
                'date_ranges' => [
                    new DateRange([
                        'start_date' => "{$days}daysAgo",
                        'end_date' => 'today',
                    ]),
                ],
                'dimensions' => [
                    new Dimension(['name' => 'pageTitle']),
                    new Dimension(['name' => 'pagePath']),
                ],
                'metrics' => [
                    new Metric(['name' => 'screenPageViews']),
                    new Metric(['name' => 'averageSessionDuration']),
                    new Metric(['name' => 'bounceRate']),
                    new Metric(['name' => 'sessions']),
                ],
                'limit' => $limit,
            ]);

            $response = $this->client->runReport($request);

            return $this->formatTopContentData($response);
        } catch (Exception $e) {
            Log::error('Failed to fetch top content analytics', [
                'error' => $e->getMessage(),
                'property_id' => $this->propertyId,
            ]);
            throw $e;
        }
    }

    /**
     * Fetch overall analytics summary
     */
    public function fetchSummary(int $days = 30): array
    {
        try {
            $request = new RunReportRequest([
                'property' => $this->propertyId,
                'date_ranges' => [
                    new DateRange([
                        'start_date' => "{$days}daysAgo",
                        'end_date' => 'today',
                    ]),
                ],
                'metrics' => [
                    new Metric(['name' => 'totalUsers']),
                    new Metric(['name' => 'sessions']),
                    new Metric(['name' => 'screenPageViews']),
                    new Metric(['name' => 'averageSessionDuration']),
                    new Metric(['name' => 'bounceRate']),
                ],
            ]);

            $response = $this->client->runReport($request);

            return $this->formatSummaryData($response);
        } catch (Exception $e) {
            Log::error('Failed to fetch analytics summary', [
                'error' => $e->getMessage(),
                'property_id' => $this->propertyId,
            ]);
            throw $e;
        }
    }

    /**
     * Fetch traffic sources data
     */
    public function fetchTrafficSources(int $days = 30, int $limit = 10, ?string $startDate = null, ?string $endDate = null): array
    {
        try {
            if ($startDate && $endDate) {
                $requestStartDate = $startDate;
                $requestEndDate = $endDate;
            } else {
                $requestStartDate = "{$days}daysAgo";
                $requestEndDate = 'today';
            }

            $request = new RunReportRequest([
                'property' => $this->propertyId,
                'date_ranges' => [
                    new DateRange([
                        'start_date' => $requestStartDate,
                        'end_date' => $requestEndDate,
                    ]),
                ],
                'dimensions' => [
                    new Dimension(['name' => 'sessionSource']),
                    new Dimension(['name' => 'sessionMedium']),
                ],
                'metrics' => [
                    new Metric(['name' => 'sessions']),
                    new Metric(['name' => 'totalUsers']),
                    new Metric(['name' => 'screenPageViews']),
                    new Metric(['name' => 'newUsers']),
                    new Metric(['name' => 'bounceRate']),
                ],
                'limit' => $limit,
            ]);

            $response = $this->client->runReport($request);

            return $this->formatTrafficSourcesData($response);
        } catch (Exception $e) {
            Log::error('Failed to fetch traffic sources analytics', [
                'error' => $e->getMessage(),
                'property_id' => $this->propertyId,
            ]);
            throw $e;
        }
    }

    /**
     * Calculate previous date range using the same logic as the controller
     */
    public function calculatePreviousDateRange(string $startDate, string $endDate, ?string $preset = null): array
    {
        $currentStartDate = \Carbon\Carbon::parse($startDate);
        $currentEndDate = \Carbon\Carbon::parse($endDate);
        $isSingleDay = $currentStartDate->isSameDay($currentEndDate);

        if ($isSingleDay) {
            $previousDate = $currentStartDate->copy()->subDay();

            return [
                'start_date' => $previousDate->format('Y-m-d'),
                'end_date' => $previousDate->format('Y-m-d'),
            ];
        }

        if ($preset === 'ytd') {
            $previousStartDate = $currentStartDate->copy()->subYear();
            $previousEndDate = $currentEndDate->copy()->subYear();

            return [
                'start_date' => $previousStartDate->format('Y-m-d'),
                'end_date' => $previousEndDate->format('Y-m-d'),
            ];
        }

        $actualCurrentDays = (int) $currentStartDate->diffInDays($currentEndDate) + 1;
        $previousEndDate = $currentStartDate->copy()->subDay();
        $previousStartDate = $previousEndDate->copy()->subDays($actualCurrentDays - 1);

        return [
            'start_date' => $previousStartDate->format('Y-m-d'),
            'end_date' => $previousEndDate->format('Y-m-d'),
        ];
    }

    /**
     * Fetch comprehensive summary with KPI metrics and trend data
     */
    public function fetchComprehensiveSummary(int $days = 30, ?string $startDate = null, ?string $endDate = null, ?string $preset = null): array
    {
        try {
            if ($startDate && $endDate) {
                $currentData = $this->fetchSummaryWithNewUsers($days, 0, $startDate, $endDate);

                $previousDateRange = $this->calculatePreviousDateRange($startDate, $endDate, $preset);

                $previousData = $this->fetchSummaryWithNewUsers(
                    $days,
                    0,
                    $previousDateRange['start_date'],
                    $previousDateRange['end_date']
                );
            } else {
                $currentData = $this->fetchSummaryWithNewUsers($days);
                $previousData = $this->fetchSummaryWithNewUsers($days, $days);
            }

            $kpiMetrics = $this->calculateKpiMetrics($currentData, $previousData);

            return [
                'total_users' => $currentData['total_users'],
                'sessions' => $currentData['sessions'],
                'page_views' => $currentData['page_views'],
                'avg_session_duration' => $currentData['avg_session_duration'],
                'bounce_rate' => $currentData['bounce_rate'],
                'formatted_users' => $currentData['formatted_users'],
                'formatted_sessions' => $currentData['formatted_sessions'],
                'formatted_page_views' => $currentData['formatted_page_views'],
                'kpi_metrics' => $kpiMetrics,
            ];
        } catch (Exception $e) {
            Log::error('Failed to fetch comprehensive analytics summary', [
                'error' => $e->getMessage(),
                'property_id' => $this->propertyId,
            ]);
            throw $e;
        }
    }

    /**
     * Fetch daily performance data for charts
     */
    public function fetchDailyPerformanceData(int $days = 30, ?string $startDate = null, ?string $endDate = null): array
    {
        try {
            if ($startDate && $endDate) {
                $requestStartDate = $startDate;
                $requestEndDate = $endDate;
            } else {
                $requestStartDate = "{$days}daysAgo";
                $requestEndDate = 'today';
            }

            $request = new RunReportRequest([
                'property' => $this->propertyId,
                'date_ranges' => [
                    new DateRange([
                        'start_date' => $requestStartDate,
                        'end_date' => $requestEndDate,
                    ]),
                ],
                'dimensions' => [
                    new Dimension(['name' => 'date']),
                ],
                'metrics' => [
                    new Metric(['name' => 'totalUsers']),
                    new Metric(['name' => 'sessions']),
                    new Metric(['name' => 'screenPageViews']),
                ],
            ]);

            $response = $this->client->runReport($request);

            return $this->formatDailyPerformanceData($response);
        } catch (Exception $e) {
            Log::error('Failed to fetch daily performance analytics', [
                'error' => $e->getMessage(),
                'property_id' => $this->propertyId,
            ]);
            throw $e;
        }
    }

    /**
     * Fetch traffic acquisition data (new vs returning users)
     */
    public function fetchTrafficAcquisitionData(int $days = 30, ?string $startDate = null, ?string $endDate = null): array
    {
        try {
            if ($startDate && $endDate) {
                $requestStartDate = $startDate;
                $requestEndDate = $endDate;
            } else {
                $requestStartDate = "{$days}daysAgo";
                $requestEndDate = 'today';
            }

            $request = new RunReportRequest([
                'property' => $this->propertyId,
                'date_ranges' => [
                    new DateRange([
                        'start_date' => $requestStartDate,
                        'end_date' => $requestEndDate,
                    ]),
                ],
                'metrics' => [
                    new Metric(['name' => 'sessions']),
                    new Metric(['name' => 'newUsers']),
                ],
            ]);

            $response = $this->client->runReport($request);

            return $this->formatTrafficAcquisitionData($response);
        } catch (Exception $e) {
            Log::error('Failed to fetch traffic acquisition analytics', [
                'error' => $e->getMessage(),
                'property_id' => $this->propertyId,
            ]);
            throw $e;
        }
    }

    /**
     * Fetch landing pages analytics data
     */
    public function fetchLandingPages(int $days = 30, int $limit = 10, ?string $startDate = null, ?string $endDate = null): array
    {
        try {
            if ($startDate && $endDate) {
                $requestStartDate = $startDate;
                $requestEndDate = $endDate;
            } else {
                $requestStartDate = "{$days}daysAgo";
                $requestEndDate = 'today';
            }

            $request = new RunReportRequest([
                'property' => $this->propertyId,
                'date_ranges' => [
                    new DateRange([
                        'start_date' => $requestStartDate,
                        'end_date' => $requestEndDate,
                    ]),
                ],
                'dimensions' => [
                    new Dimension(['name' => 'pagePath']),
                    new Dimension(['name' => 'pageTitle']),
                ],
                'metrics' => [
                    new Metric(['name' => 'sessions']),
                    new Metric(['name' => 'newUsers']),
                    new Metric(['name' => 'totalUsers']),
                    new Metric(['name' => 'bounceRate']),
                ],
                'limit' => $limit,
            ]);

            $response = $this->client->runReport($request);

            return $this->formatLandingPagesData($response);
        } catch (Exception $e) {
            Log::error('Failed to fetch landing pages analytics', [
                'error' => $e->getMessage(),
                'property_id' => $this->propertyId,
            ]);
            throw $e;
        }
    }

    /**
     * Check if analytics is properly configured
     */
    public static function isConfigured(): bool
    {
        return GoogleAnalyticsClientFactory::isConfigured();
    }

    /**
     * Get property ID
     */
    public function getPropertyId(): string
    {
        return $this->propertyId;
    }

    /**
     * Fetch summary data including new users
     */
    private function fetchSummaryWithNewUsers(int $days = 30, int $offsetDays = 0, ?string $startDate = null, ?string $endDate = null): array
    {
        if ($startDate && $endDate) {
            $requestStartDate = $startDate;
            $requestEndDate = $endDate;
        } else {
            $requestStartDate = $offsetDays > 0 ? "{$offsetDays}daysAgo" : "{$days}daysAgo";
            $requestEndDate = $offsetDays > 0 ? ($offsetDays - $days + 1).'daysAgo' : 'today';
        }

        $request = new RunReportRequest([
            'property' => $this->propertyId,
            'date_ranges' => [
                new DateRange([
                    'start_date' => $requestStartDate,
                    'end_date' => $requestEndDate,
                ]),
            ],
            'metrics' => [
                new Metric(['name' => 'totalUsers']),
                new Metric(['name' => 'sessions']),
                new Metric(['name' => 'screenPageViews']),
                new Metric(['name' => 'averageSessionDuration']),
                new Metric(['name' => 'bounceRate']),
                new Metric(['name' => 'newUsers']),
            ],
        ]);

        $response = $this->client->runReport($request);

        return $this->formatSummaryWithNewUsersData($response);
    }

    /**
     * Format summary data with new users
     */
    private function formatSummaryWithNewUsersData($response): array
    {
        $row = $response->getRows()[0] ?? null;

        if (! $row) {
            return [
                'total_users' => 0,
                'sessions' => 0,
                'page_views' => 0,
                'avg_session_duration' => '00:00:00',
                'bounce_rate' => 0,
                'new_users' => 0,
                'returning_users' => 0,
                'formatted_users' => '0',
                'formatted_sessions' => '0',
                'formatted_page_views' => '0',
            ];
        }

        $metricValues = $row->getMetricValues();
        $totalUsers = (int) $metricValues[0]->getValue();
        $newUsers = (int) $metricValues[5]->getValue();
        $returningUsers = $totalUsers - $newUsers;

        return [
            'total_users' => $totalUsers,
            'sessions' => (int) $metricValues[1]->getValue(),
            'page_views' => (int) $metricValues[2]->getValue(),
            'avg_session_duration' => $this->formatDuration((int) $metricValues[3]->getValue()),
            'bounce_rate' => round((float) $metricValues[4]->getValue() * 100, 2),
            'new_users' => $newUsers,
            'returning_users' => $returningUsers,
            'formatted_users' => number_format($totalUsers),
            'formatted_sessions' => number_format((int) $metricValues[1]->getValue()),
            'formatted_page_views' => number_format((int) $metricValues[2]->getValue()),
        ];
    }

    /**
     * Calculate KPI metrics with trend data
     */
    private function calculateKpiMetrics(array $currentData, array $previousData): array
    {
        $metrics = [];

        $metrics['sessions'] = $this->calculateMetricTrend(
            $currentData['sessions'],
            $previousData['sessions'],
            $currentData['formatted_sessions']
        );

        $metrics['total_users'] = $this->calculateMetricTrend(
            $currentData['total_users'],
            $previousData['total_users'],
            $currentData['formatted_users']
        );

        $metrics['new_users'] = $this->calculateMetricTrend(
            $currentData['new_users'],
            $previousData['new_users'],
            number_format($currentData['new_users'])
        );

        $metrics['returning_users'] = $this->calculateMetricTrend(
            $currentData['returning_users'],
            $previousData['returning_users'],
            number_format($currentData['returning_users'])
        );

        $bounceRateCurrent = $currentData['bounce_rate'];
        $bounceRatePrevious = $previousData['bounce_rate'];
        $bounceRateChange = $bounceRateCurrent - $bounceRatePrevious;
        $bounceRateChangePercent = $previousData['bounce_rate'] > 0
            ? ($bounceRateChange / $previousData['bounce_rate']) * 100
            : 0;

        $metrics['bounce_rate'] = [
            'current' => number_format($bounceRateCurrent, 2).'%',
            'relative_change' => ($bounceRateChangePercent >= 0 ? '+' : '').number_format($bounceRateChangePercent, 1).'%',
            'absolute_change' => ($bounceRateChange >= 0 ? '+' : '').number_format($bounceRateChange, 2).'%',
            'is_positive' => $bounceRateChange > 0,
        ];

        $currentDuration = $this->parseDuration($currentData['avg_session_duration']);
        $previousDuration = $this->parseDuration($previousData['avg_session_duration']);
        $durationChange = $currentDuration - $previousDuration;
        $durationChangePercent = $previousDuration > 0
            ? ($durationChange / $previousDuration) * 100
            : 0;

        $metrics['avg_session_duration'] = [
            'current' => $this->normalizeDuration($currentData['avg_session_duration']),
            'relative_change' => ($durationChangePercent >= 0 ? '+' : '').number_format($durationChangePercent, 1).'%',
            'absolute_change' => ($durationChange >= 0 ? '+' : '').$this->normalizeDuration($this->formatDuration(abs($durationChange))),
            'is_positive' => $durationChange > 0,
        ];

        return $metrics;
    }

    /**
     * Calculate trend for a metric
     */
    private function calculateMetricTrend(int $current, int $previous, string $formattedCurrent): array
    {
        $change = $current - $previous;
        $changePercent = $previous > 0 ? ($change / $previous) * 100 : 0;

        return [
            'current' => $formattedCurrent,
            'relative_change' => ($changePercent >= 0 ? '+' : '').number_format($changePercent, 1).'%',
            'absolute_change' => ($change >= 0 ? '+' : '').number_format($change),
            'is_positive' => $change > 0,
        ];
    }

    /**
     * Parse duration string to seconds
     */
    private function parseDuration(string $duration): int
    {
        $parts = explode(':', $duration);
        if (count($parts) === 2) {
            return (int) $parts[0] * 60 + (int) $parts[1];
        } elseif (count($parts) === 3) {
            return (int) $parts[0] * 3600 + (int) $parts[1] * 60 + (int) $parts[2];
        }

        return 0;
    }

    private function normalizeDuration(string $duration): string
    {
        $parts = explode(':', $duration);

        if (count($parts) === 2) {
            return '00:'.str_pad($parts[0], 2, '0', STR_PAD_LEFT).':'.str_pad($parts[1], 2, '0', STR_PAD_LEFT);
        } elseif (count($parts) === 3) {
            return str_pad($parts[0], 2, '0', STR_PAD_LEFT).':'.
                str_pad($parts[1], 2, '0', STR_PAD_LEFT).':'.
                str_pad($parts[2], 2, '0', STR_PAD_LEFT);
        }

        return '00:00:00';
    }

    /**
     * Format top content data
     */
    private function formatTopContentData($response): array
    {
        $data = [];
        $rows = $response->getRows();

        if (empty($rows)) {
            return $data;
        }

        foreach ($rows as $index => $row) {
            $dimensionValues = $row->getDimensionValues();
            $metricValues = $row->getMetricValues();

            $data[] = [
                'rank' => $index + 1,
                'title' => $dimensionValues[0]->getValue() ?: 'Untitled',
                'path' => $dimensionValues[1]->getValue() ?: '/',
                'views' => (int) $metricValues[0]->getValue(),
                'avg_duration' => $this->formatDuration((int) $metricValues[1]->getValue()),
                'bounce_rate' => round((float) $metricValues[2]->getValue(), 2),
                'sessions' => (int) $metricValues[3]->getValue(),
                'formatted_views' => number_format((int) $metricValues[0]->getValue()),
                'formatted_sessions' => number_format((int) $metricValues[3]->getValue()),
            ];
        }

        return $data;
    }

    /**
     * Format summary data
     */
    private function formatSummaryData($response): array
    {
        $row = $response->getRows()[0] ?? null;

        if (! $row) {
            return [];
        }

        $metricValues = $row->getMetricValues();

        return [
            'total_users' => (int) $metricValues[0]->getValue(),
            'sessions' => (int) $metricValues[1]->getValue(),
            'page_views' => (int) $metricValues[2]->getValue(),
            'avg_session_duration' => $this->formatDuration((int) $metricValues[3]->getValue()),
            'bounce_rate' => round((float) $metricValues[4]->getValue(), 2),
            'formatted_users' => number_format((int) $metricValues[0]->getValue()),
            'formatted_sessions' => number_format((int) $metricValues[1]->getValue()),
            'formatted_page_views' => number_format((int) $metricValues[2]->getValue()),
        ];
    }

    /**
     * Format traffic sources data
     */
    private function formatTrafficSourcesData($response): array
    {
        $data = [];
        $rows = $response->getRows();

        if (empty($rows)) {
            return $data;
        }

        foreach ($rows as $row) {
            $dimensionValues = $row->getDimensionValues();
            $metricValues = $row->getMetricValues();

            $sessions = (int) $metricValues[0]->getValue();
            $users = (int) $metricValues[1]->getValue();
            $pageViews = (int) $metricValues[2]->getValue();
            $newUsers = (int) $metricValues[3]->getValue();
            $bounceRate = round((float) $metricValues[4]->getValue() * 100, 2);

            $data[] = [
                'source' => $dimensionValues[0]->getValue().' / '.$dimensionValues[1]->getValue(),
                'sessions' => $sessions,
                'newUsers' => $newUsers,
                'totalUsers' => $users,
                'bounceRate' => $bounceRate,
                'users' => $users,
                'page_views' => $pageViews,
                'new_users' => $newUsers,
                'formatted_sessions' => number_format($sessions),
                'formatted_users' => number_format($users),
                'formatted_page_views' => number_format($pageViews),
            ];
        }

        return $data;
    }

    /**
     * Format traffic acquisition data (sessions based)
     */
    private function formatTrafficAcquisitionData($response): array
    {
        $row = $response->getRows()[0] ?? null;

        if (! $row) {
            return [
                'new_sessions' => 0,
                'new_sessions_percentage' => 0,
                'returning_sessions' => 0,
                'returning_sessions_percentage' => 0,
            ];
        }

        $metricValues = $row->getMetricValues();
        $totalSessions = (int) $metricValues[0]->getValue();
        $newSessions = (int) $metricValues[1]->getValue();
        $returningSessions = $totalSessions - $newSessions;

        $newSessionsPercentage = $totalSessions > 0 ? round(($newSessions / $totalSessions) * 100, 1) : 0;
        $returningSessionsPercentage = $totalSessions > 0 ? round(($returningSessions / $totalSessions) * 100, 1) : 0;

        return [
            'new_sessions' => $newSessions,
            'new_sessions_percentage' => $newSessionsPercentage,
            'returning_sessions' => $returningSessions,
            'returning_sessions_percentage' => $returningSessionsPercentage,
        ];
    }

    /**
     * Format daily performance data
     */
    private function formatDailyPerformanceData($response): array
    {
        $data = [];
        $rows = $response->getRows();

        if (empty($rows)) {
            return $data;
        }

        foreach ($rows as $row) {
            $dimensionValues = $row->getDimensionValues();
            $metricValues = $row->getMetricValues();

            $date = $dimensionValues[0]->getValue();

            $formattedDate = $this->formatGoogleAnalyticsDate($date);

            $data[] = [
                'date' => $formattedDate,
                'users' => (int) $metricValues[0]->getValue(),
                'sessions' => (int) $metricValues[1]->getValue(),
                'page_views' => (int) $metricValues[2]->getValue(),
            ];
        }

        return $data;
    }

    /**
     * Format landing pages data
     */
    private function formatLandingPagesData($response): array
    {
        $data = [];
        $rows = $response->getRows();

        if (empty($rows)) {
            return $data;
        }

        $totalSessions = 0;
        foreach ($rows as $row) {
            $metricValues = $row->getMetricValues();
            $totalSessions += (int) $metricValues[0]->getValue();
        }

        foreach ($rows as $index => $row) {
            $dimensionValues = $row->getDimensionValues();
            $metricValues = $row->getMetricValues();

            $sessions = (int) $metricValues[0]->getValue();
            $newUsers = (int) $metricValues[1]->getValue();
            $totalUsers = (int) $metricValues[2]->getValue();
            $bounceRate = round((float) $metricValues[3]->getValue() * 100, 2);

            $percentage = $totalSessions > 0 ? round(($sessions / $totalSessions) * 100, 2) : 0;

            $data[] = [
                'rank' => $index + 1,
                'landing_page' => $dimensionValues[0]->getValue() ?: '/',
                'page_title' => $dimensionValues[1]->getValue() ?: 'Untitled',
                'sessions' => $sessions,
                'percentage' => $percentage,
                'new_users' => $newUsers,
                'total_users' => $totalUsers,
                'bounce_rate' => $bounceRate,
                'formatted_sessions' => number_format($sessions),
                'formatted_new_users' => number_format($newUsers),
                'formatted_total_users' => number_format($totalUsers),
            ];
        }

        return $data;
    }

    /**
     * Convert Google Analytics date format (YYYYMMDD) to standard format (YYYY-MM-DD)
     */
    private function formatGoogleAnalyticsDate(string $date): string
    {
        if (strlen($date) === 8) {
            $year = substr($date, 0, 4);
            $month = substr($date, 4, 2);
            $day = substr($date, 6, 2);

            return "{$year}-{$month}-{$day}";
        }

        return $date;
    }

    /**
     * Format duration from seconds to human readable format
     */
    private function formatDuration(int $seconds): string
    {
        if ($seconds < 60) {
            return "0:{$seconds}";
        }

        $minutes = floor($seconds / 60);
        $remainingSeconds = $seconds % 60;

        return sprintf('%d:%02d', $minutes, $remainingSeconds);
    }
}
