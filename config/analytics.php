<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Google Analytics Property ID
    |--------------------------------------------------------------------------
    |
    | Your Google Analytics 4 property ID. You can find this in your GA4
    | property settings under Admin > Property Settings > Property ID.
    |
    */
    'property_id' => env('GOOGLE_ANALYTICS_PROPERTY_ID'),

    /*
    |--------------------------------------------------------------------------
    | Google Analytics Service Account Credentials
    |--------------------------------------------------------------------------
    |
    | Path to your Google Analytics service account JSON credentials file.
    |
    | Configuration:
    | 1. Set only the filename in .env:
    |    GOOGLE_ANALYTICS_CREDENTIALS_JSON=service-account-credentials.json
    |
    | 2. Place the JSON file in storage/app/analytics/ directory
    |
    | Example:
    | - .env: GOOGLE_ANALYTICS_CREDENTIALS_JSON=my-credentials.json
    | - File: storage/app/analytics/my-credentials.json
    | - Result: Full path automatically resolved
    |
    */
    'service_account_credentials_json' => storage_path('app/analytics/'.env('GOOGLE_ANALYTICS_CREDENTIALS_JSON', 'service-account-credentials.json')),

    /*
    |--------------------------------------------------------------------------
    | Analytics Assets Path
    |--------------------------------------------------------------------------
    |
    | The path where Analytics assets (CSS, JS, images) will be published
    | relative to the public directory. This allows customization of where
    | the dashboard assets are served from.
    |
    */
    'assets_path' => env('ANALYTICS_ASSETS_PATH', 'vendor/analytics'),

    /*
    |--------------------------------------------------------------------------
    | Caching Configuration
    |--------------------------------------------------------------------------
    |
    | Configure how analytics data is cached to improve performance and
    | reduce API calls to Google Analytics.
    |
    */
    'cache' => [
        'duration' => env('ANALYTICS_CACHE_DURATION', 3600), // Cache duration in seconds
        'store' => env('ANALYTICS_CACHE_STORE', null),        // Laravel cache store to use (null = use default)
        'prefix' => 'analytics:',                             // Cache key prefix
    ],

    /*
    |--------------------------------------------------------------------------
    | Dashboard Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the analytics dashboard interface including routing,
    | middleware, and access control.
    |
    */
    'dashboard' => [
        'enabled' => env('ANALYTICS_DASHBOARD_ENABLED', true),
        'middleware' => ['web', 'auth'],
        'route_prefix' => 'analytics',
        'title' => 'Analytics Dashboard',
    ],

    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the analytics API endpoints including authentication,
    | rate limiting, and middleware.
    |
    */
    'api' => [
        'enabled' => env('ANALYTICS_API_ENABLED', true),
        'middleware' => ['web', 'auth'],
        'route_prefix' => 'analytics/api',
        'rate_limit' => 60,
    ],

    /*
    |--------------------------------------------------------------------------
    | Date Range Configuration
    |--------------------------------------------------------------------------
    |
    | Configure default date ranges and presets for analytics queries.
    |
    */
    'date_ranges' => [
        'default' => '7daysAgo',
        'presets' => [
            '7daysAgo' => '7 days',
            '30daysAgo' => '30 days',
            '90daysAgo' => '90 days',
        ],
        'max_range_days' => 365,
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination Configuration
    |--------------------------------------------------------------------------
    |
    | Configure pagination settings for analytics data tables.
    |
    */
    'pagination' => [
        'per_page' => 10,
        'max_per_page' => 100,
    ],

    /*
    |--------------------------------------------------------------------------
    | Retry Configuration
    |--------------------------------------------------------------------------
    |
    | Configure retry logic for failed Google Analytics API requests.
    |
    */
    'retry' => [
        'max_attempts' => 3,
        'delay_milliseconds' => 500,
    ],
];
