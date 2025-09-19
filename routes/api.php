<?php

use Illuminate\Support\Facades\Route;
use Tobidsn\LaravelAnalytics\Http\Controllers\DailyChartController;
use Tobidsn\LaravelAnalytics\Http\Controllers\KpiMetricsController;
use Tobidsn\LaravelAnalytics\Http\Controllers\LandingPagesController;
use Tobidsn\LaravelAnalytics\Http\Controllers\TrafficChartController;
use Tobidsn\LaravelAnalytics\Http\Controllers\TrafficTableController;

/*
|--------------------------------------------------------------------------
| Analytics Package API Routes
|--------------------------------------------------------------------------
|
| These API routes provide RESTful endpoints for accessing Google Analytics
| data. They are automatically prefixed with 'api/analytics' and require
| API authentication.
|
*/

// KPI Metrics endpoint
Route::get('/kpi-metrics', KpiMetricsController::class)
    ->name('analytics.api.kpi-metrics');

// Daily chart data endpoint
Route::get('/daily-chart', DailyChartController::class)
    ->name('analytics.api.daily-chart');

// Traffic acquisition chart endpoint
Route::get('/traffic-chart', TrafficChartController::class)
    ->name('analytics.api.traffic-chart');

// Traffic acquisition table endpoint (paginated)
Route::get('/traffic-table', TrafficTableController::class)
    ->name('analytics.api.traffic-table');

// Landing pages table endpoint (paginated)
Route::get('/landing-pages', LandingPagesController::class)
    ->name('analytics.api.landing-pages');
