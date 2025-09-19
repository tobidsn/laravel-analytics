# Laravel Analytics Package

Google Analytics (GA4) dashboard for your Laravel app. Just install, configure, and visit `/analytics` to view your site statistics.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/tobidsn/laravel-analytics.svg?style=flat-square)](https://packagist.org/packages/tobidsn/laravel-analytics)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/tobidsn/laravel-analytics/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/tobidsn/laravel-analytics/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/tobidsn/laravel-analytics/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/tobidsn/laravel-analytics/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/tobidsn/laravel-analytics.svg?style=flat-square)](https://packagist.org/packages/tobidsn/laravel-analytics)

## Features

- **Google Analytics GA4 Integration**: Complete dashboard for Laravel applications
- **Zero Configuration Frontend**: Pre-built assets included, no Node.js required
- **Simple Setup**: Just install, configure GA4 credentials, and use
- **Responsive Dashboard**: Modern UI built with Tailwind CSS and React
- **Real-time Data**: Fetch visitors, page views, referrers, and device statistics
- **Time Period Selection**: View analytics for different date ranges
- **API Endpoints**: RESTful API for programmatic access
- **Built-in Caching**: Reduce API calls to Google Analytics
- **Permission Management**: Role-based access control

## Dashboard Components

- **Overview Cards**: Total visitors, page views, bounce rate, session duration
- **Daily Performance Chart**: Line chart showing visitor trends over time
- **Top Landing Pages**: Table showing most visited pages with metrics
- **Traffic Sources**: Chart displaying referrer breakdown (direct, search, social, etc.)
- **KPI Metrics**: Individual metric displays with comparisons
- **Device Analytics**: Mobile vs desktop traffic distribution
- **Date Range Filters**: Quick select buttons and custom date picker

## Requirements

- **PHP**: ^8.2
- **Laravel**: ^10.0 or ^11.0 or ^12.0
- **Extensions**: ext-json, ext-curl
- **Google Analytics**: GA4 property with Admin access

## Installation

### Quick Installation (Recommended)

```bash
# 1. Install via Composer
composer require tobidsn/laravel-analytics

# 2. Run installation command
php artisan analytics:install
```

The `analytics:install` command will:
- Publish configuration files
- Publish frontend assets (CSS/JS) to your public directory
- Display setup instructions

> **Note**: Views are not published by default. Use `--views` flag if you need to customize templates.

### Custom Installation Options

The default installation publishes assets and configuration. For more control:

```bash
# Default: assets + configuration only
php artisan analytics:install

# Install only configuration
php artisan analytics:install --config

# Install only assets (CSS/JS files)
php artisan analytics:install --assets

# Install only views (for customization)
php artisan analytics:install --views

# Force overwrite existing files
php artisan analytics:install --force
```

### Manual Installation (Alternative)

If you prefer manual control:

```bash
# Install package
composer require tobidsn/laravel-analytics

# Publish configuration
php artisan vendor:publish --tag="analytics-config"

# Publish views (optional, for customization)
php artisan vendor:publish --tag="analytics-views"

# Publish assets (optional, for custom path)
php artisan vendor:publish --tag="analytics-assets"
```

### Asset Configuration

By default, assets are served from `public/vendor/analytics/`. To customize the asset path:

1. **Set custom path in `.env`**:
   ```env
   ANALYTICS_ASSETS_PATH=custom/analytics/path
   ```

2. **Publish assets to custom location**:
   ```bash
   php artisan analytics:install --assets
   ```

> **Note**: Frontend assets (CSS/JS) are pre-built and included with the package. No Node.js or build steps required!

## Google Analytics Setup

### 1. Create Service Account

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create new project or select existing
3. Enable Google Analytics Reporting API
4. Create service account with Analytics Reader role
5. Download JSON credentials file

### 2. Configure GA4 Property

1. Add service account email to GA4 property with Viewer permissions
2. Copy Property ID from GA4 Admin settings

### 3. Environment Configuration

Add these variables to your `.env` file:

```env
# Required: Google Analytics configuration
GOOGLE_ANALYTICS_PROPERTY_ID=your-ga4-property-id
GOOGLE_ANALYTICS_CREDENTIALS_JSON=service-account-credentials.json

# Optional: Asset publishing configuration
ANALYTICS_ASSETS_PATH=vendor/analytics

# Optional: Dashboard and API settings
ANALYTICS_CACHE_DURATION=3600
ANALYTICS_DASHBOARD_ENABLED=true
ANALYTICS_API_ENABLED=true
```

### 4. Place Credentials File

Place your Google Analytics service account JSON file at:
```
storage/app/analytics/service-account-credentials.json
```

> **Tip**: You can use any filename for your credentials file. Just update the `GOOGLE_ANALYTICS_CREDENTIALS_JSON` environment variable to match your filename.

## Configuration

The published configuration file (`config/analytics.php`) contains:

```php
return [
    'property_id' => env('GOOGLE_ANALYTICS_PROPERTY_ID'),
    
    'credentials_path' => env('GOOGLE_ANALYTICS_CREDENTIALS_PATH'),
    
    // Asset publishing configuration
    'assets_path' => env('ANALYTICS_ASSETS_PATH', 'vendor/analytics'),
    
    'cache' => [
        'duration' => env('ANALYTICS_CACHE_DURATION', 3600),
        'store' => env('ANALYTICS_CACHE_STORE', 'default'),
        'prefix' => 'analytics:',
    ],
    
    'dashboard' => [
        'enabled' => env('ANALYTICS_DASHBOARD_ENABLED', true),
        'middleware' => ['web', 'auth'],
        'route_prefix' => 'analytics',
        'title' => 'Analytics Dashboard',
    ],
    
    'api' => [
        'enabled' => env('ANALYTICS_API_ENABLED', true),
        'middleware' => ['api', 'auth:sanctum'],
        'route_prefix' => 'api/analytics',
        'rate_limit' => 60,
    ],
    
    'date_ranges' => [
        'default' => '7daysAgo',
        'presets' => [
            '7daysAgo' => '7 days',
            '30daysAgo' => '30 days',
            '90daysAgo' => '90 days',
        ],
        'max_range_days' => 365,
    ],
    
    'pagination' => [
        'per_page' => 10,
        'max_per_page' => 100,
    ],
    
    'retry' => [
        'max_attempts' => 3,
        'delay_milliseconds' => 500,
    ],
];
```

### Key Configuration Options

- **`assets_path`**: Customize where frontend assets are published (default: `vendor/analytics`)
- **`dashboard.enabled`**: Enable/disable the analytics dashboard route
- **`api.enabled`**: Enable/disable API endpoints
- **`cache.duration`**: How long to cache GA4 API responses (seconds)
- **`dashboard.middleware`**: Middleware applied to dashboard routes
- **`api.middleware`**: Middleware applied to API routes

## Usage

### Dashboard Access

Visit `/analytics` in your Laravel application to view the dashboard.

### Programmatic Usage

```php
use Tobidsn\LaravelAnalytics\Facades\Analytics;

// Get visitors for last 7 days
$visitors = Analytics::visitors()->lastDays(7);

// Get page views for specific date range
$pageViews = Analytics::pageViews()
    ->startDate('2024-01-01')
    ->endDate('2024-01-31')
    ->get();

// Get most visited pages
$topPages = Analytics::pages()->mostVisited(10);

// Get traffic sources
$sources = Analytics::sources()->breakdown();
```

### API Endpoints

The package automatically registers these routes:

```php
GET /analytics                    // Dashboard view
GET /api/analytics/kpi           // KPI metrics
GET /api/analytics/daily-chart   // Daily performance data
GET /api/analytics/landing-pages // Top landing pages
GET /api/analytics/traffic-chart // Traffic acquisition chart
GET /api/analytics/traffic-table // Traffic acquisition table
```

### Blade Components

```blade
{{-- Dashboard container --}}
<x-analytics::dashboard />

{{-- Individual widgets --}}
<x-analytics::visitors-chart :days="30" />
<x-analytics::overview-cards />
<x-analytics::top-pages :limit="10" />
<x-analytics::traffic-sources />
```

## Testing

### Run Tests

```bash
composer test
```

### Run Tests with Coverage

```bash
composer test-coverage
```

### Static Analysis

```bash
composer analyse
```

### Code Formatting

```bash
composer format
```

### Local Testing in Laravel Project

To test this package in your local Laravel application:

#### Method 1: Path Repository (Recommended)

1. **Add Path Repository** to your Laravel project's `composer.json`:
   ```json
   {
       "repositories": [
           {
               "type": "path",
               "url": "/Users/tobi/Sites/laravel-package/laravel-analytics"
           }
       ],
       "require": {
           "tobidsn/laravel-analytics": "@dev"
       }
   }
   ```

   > **Note**: Update the `url` path to match your actual package location. Common patterns:
   > - Absolute path: `/Users/tobi/Sites/laravel-package/laravel-analytics`
   > - Relative path: `../laravel-analytics` (if package is in parent directory)
   > - Same level: `./laravel-analytics` (if package is in same directory)

2. **Install Package**:
   ```bash
   composer require tobidsn/laravel-analytics:@dev
   ```

 3. **Install and Configure**:
   ```bash
   # Run the installation command
   php artisan analytics:install
   
   # Or manually publish configuration
   php artisan vendor:publish --tag="analytics-config"
   ```

4. **Configure Environment** (add to `.env`):
   ```env
   GOOGLE_ANALYTICS_PROPERTY_ID=your-property-id
   GOOGLE_ANALYTICS_CREDENTIALS_PATH=/path/to/credentials.json
   ANALYTICS_DASHBOARD_ENABLED=true
   ANALYTICS_API_ENABLED=true
   ```

5. **Test Dashboard**:
   ```bash
   php artisan serve
   # Visit: http://localhost:8000/analytics
   ```

#### Method 2: Quick Setup Script

Use the included test script for automated setup:

```bash
# From the package directory
./test-local.sh /path/to/your/laravel-project
```

#### Troubleshooting Local Testing

- **Path repository does not exist**: 
  - Verify the exact path to your package directory
  - Use absolute path: `/full/path/to/laravel-analytics`
  - Check current directory: `pwd` in package folder
- **PSR-4 autoloading errors**: 
  - Run `composer dump-autoload` in your Laravel project
  - Ensure all class names match their file names
- **Routes not registered**: 
  - Clear route cache: `php artisan route:clear`
  - Check config: `php artisan config:show analytics`
- **Class not found**: 
  - Run `composer dump-autoload`
  - Verify service provider is registered: `php artisan package:discover`
- **Config not loaded**: 
  - Clear config cache: `php artisan config:clear`
  - Publish config: `php artisan vendor:publish --tag="analytics-config"`
- **Check package installation**: `composer show tobidsn/laravel-analytics`
- **List analytics routes**: `php artisan route:list | grep analytics`

#### Common Setup Issues

**Missing Routes File Error**:
```bash
# If you see "Failed to open stream: No such file or directory" for routes
php artisan route:clear
php artisan config:clear
```

**Service Provider Not Found**:
```bash
# Clear package discovery cache
php artisan package:discover --ansi
```

**Google Analytics API Errors**:
```bash
# Test connection
php artisan analytics:test-connection
```

#### Alternative Setup Methods

**Method A: Symlink (Quick)**
```bash
cd /path/to/your/laravel-project/vendor
mkdir -p tobidsn
ln -s /Users/tobi/Sites/laravel-package/laravel-analytics tobidsn/laravel-analytics
composer dump-autoload
```

**Method B: Composer Config**
```bash
cd /path/to/your/laravel-project
composer config repositories.laravel-analytics path /Users/tobi/Sites/laravel-package/laravel-analytics
composer require tobidsn/laravel-analytics:@dev
```

**Method C: Direct Copy (Last Resort)**
```bash
cp -r /Users/tobi/Sites/laravel-package/laravel-analytics /path/to/your/laravel-project/vendor/tobidsn/
composer dump-autoload
```

#### Debug Commands

```bash
# Check if package is properly installed
composer show tobidsn/laravel-analytics

# Verify service provider registration
php artisan package:discover

# Check configuration
php artisan config:show analytics

# Test API endpoints
curl -H "Accept: application/json" http://localhost:8000/api/analytics/kpi
```

## Development

### Frontend Development (Contributors Only)

> **Note**: End users don't need these commands. Frontend assets are pre-built.

```bash
# Install dependencies
npm install

# Development server (with HMR)
npm run dev

# Build for production
npm run build
```

### Asset Distribution Strategy

This package follows a **zero-configuration** approach:
- All CSS and JavaScript files are pre-built and included in `public/vendor/analytics/`
- Eliminates the need for users to have Node.js or run build commands
- Assets are automatically registered via the service provider
- Just install via Composer and you're ready to go!

## Troubleshooting

### Common Issues

1. **Google Analytics API Errors**:
   - Verify service account permissions in GA4
   - Check property ID configuration
   - Ensure Google Analytics Reporting API is enabled

2. **Authentication Problems**:
   - Validate credentials file path and format
   - Check service account email is added to GA4 users
   - Verify file permissions for credentials.json

3. **Dashboard Not Loading**:
   - Check browser console for JavaScript errors
   - Verify middleware configuration
   - Clear cache: `php artisan cache:clear`

4. **Missing Data**:
   - Check GA4 data collection setup
   - Verify tracking code installation
   - Review date range and filters

## Performance

### Caching

The package uses Laravel's built-in cache system:
- **No database required**: Uses your configured cache driver (Redis, file, array, etc.)
- **Configurable duration**: Set `ANALYTICS_CACHE_DURATION` in seconds (default: 3600)
- **Cache store**: Optionally specify cache store with `ANALYTICS_CACHE_STORE`
- **Automatic prefixing**: All cache keys prefixed with `analytics:`
- **GA4 API responses**: Cached to reduce API calls and improve performance

### Optimization

- Minified and optimized frontend assets
- CDN-ready asset distribution
- Efficient database queries with proper indexing
- Rate limiting for API endpoints

## Security

- **Input Sanitization**: All user inputs properly validated
- **CSRF Protection**: Cross-site request forgery protection
- **Authentication**: Integration with Laravel's auth system
- **Rate Limiting**: API abuse protection
- **Permission Checks**: Role-based access control

## Contributing

### Development Setup

1. **Clone Repository**:
   ```bash
   git clone https://github.com/tobidsn/laravel-analytics.git
   cd laravel-analytics
   ```

2. **Install Dependencies**:
   ```bash
   composer install
   npm install  # Only for frontend development
   ```

3. **Run Tests**:
   ```bash
   composer test
   ```

### Contributing Guidelines

- Fork the repository and create feature branches
- Follow existing code style and conventions
- Write tests for all new functionality
- Update documentation for API changes
- Submit pull requests with detailed descriptions

### Code Style

- **PHP**: Follow PSR-12 standards
- **Laravel**: Use Laravel best practices
- **Testing**: Use Pest framework
- **Frontend**: React with modern patterns

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [tobidsn](https://github.com/tobidsn)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
