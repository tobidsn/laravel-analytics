#!/bin/bash

# Laravel Analytics Package - Local Testing Script
# Usage: ./test-local.sh /path/to/your/laravel-project

if [ -z "$1" ]; then
    echo "Usage: ./test-local.sh /path/to/your/laravel-project"
    exit 1
fi

LARAVEL_PROJECT_PATH="$1"
PACKAGE_PATH="$(pwd)"  # Use current directory (where script is run from)

echo "🔧 Setting up Laravel Analytics package for local testing..."
echo "📦 Package path: $PACKAGE_PATH"
echo "🎯 Laravel project: $LARAVEL_PROJECT_PATH"

# Check if Laravel project exists
if [ ! -d "$LARAVEL_PROJECT_PATH" ]; then
    echo "❌ Laravel project not found at: $LARAVEL_PROJECT_PATH"
    exit 1
fi

# Check if we're in the package directory
if [ ! -f "composer.json" ] || ! grep -q "tobidsn/laravel-analytics" composer.json; then
    echo "❌ Please run this script from the laravel-analytics package directory"
    exit 1
fi

cd "$LARAVEL_PROJECT_PATH"

echo "📦 Adding path repository to composer.json..."

# Backup composer.json
cp composer.json composer.json.backup

# Add path repository if not exists
if ! grep -q "laravel-analytics" composer.json; then
    # Create temporary composer.json with path repository
    jq --arg path "$PACKAGE_PATH" '
        .repositories = [{"type": "path", "url": $path}] + (.repositories // []) |
        .require["tobidsn/laravel-analytics"] = "@dev"
    ' composer.json > composer.json.tmp && mv composer.json.tmp composer.json
fi

echo "🎯 Installing package..."
composer require tobidsn/laravel-analytics:@dev

echo "📋 Publishing configuration..."
php artisan vendor:publish --tag="analytics-config" --force

echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear

echo "🔍 Checking routes..."
php artisan route:list | grep analytics

echo ""
echo "✅ Setup complete! Next steps:"
echo "1. Configure Google Analytics credentials in .env:"
echo "   GOOGLE_ANALYTICS_PROPERTY_ID=your-property-id"
echo "   GOOGLE_ANALYTICS_CREDENTIALS_JSON=service-account-credentials.json"
echo ""
echo "2. Place your service account JSON file at:"
echo "   storage/app/analytics/service-account-credentials.json"
echo ""
echo "2. Start development server:"
echo "   php artisan serve"
echo ""
echo "3. Visit dashboard:"
echo "   http://localhost:8000/analytics"
echo ""
echo "4. Test API endpoints:"
echo "   curl http://localhost:8000/api/analytics/kpi"