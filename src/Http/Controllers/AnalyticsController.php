<?php

namespace Tobidsn\LaravelAnalytics\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Tobidsn\LaravelAnalytics\Analytics;

class AnalyticsController extends Controller
{
    protected Analytics $analytics;

    public function __construct(Analytics $analytics)
    {
        $this->analytics = $analytics;
    }

    public function index(Request $request)
    {
        $days = $request->get('days', 30);

        try {
            $data = [
                'visitorsAndPageViews' => $this->analytics->getVisitorsAndPageViews($days),
                'totalVisitorsAndPageViews' => $this->analytics->getTotalVisitorsAndPageViews($days),
                'mostVisitedPages' => $this->analytics->getMostVisitedPages($days),
                'topReferrers' => $this->analytics->getTopReferrers($days),
                'userTypes' => $this->analytics->getUserTypes($days),
                'topBrowsers' => $this->analytics->getTopBrowsers($days),
                'days' => $days,
            ];
        } catch (\Exception $e) {
            $data = [
                'error' => 'Unable to fetch analytics data: '.$e->getMessage(),
                'days' => $days,
            ];
        }

        return view('laravel-analytics::dashboard', $data);
    }
}
