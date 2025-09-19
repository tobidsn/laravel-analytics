<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Analytics Package Web Routes
|--------------------------------------------------------------------------
|
| These routes provide the web dashboard interface for viewing Google
| Analytics data. They are automatically prefixed with the configured
| route prefix (default: 'analytics').
|
*/

// Dashboard route - main analytics dashboard view
Route::get('/', function () {
    return view('analytics::dashboard');
})->name('analytics.dashboard');

/*
|--------------------------------------------------------------------------
| Asset Serving Routes
|--------------------------------------------------------------------------
|
| These routes serve the package assets (CSS, JS) directly from the package
| directory if they haven't been published to the public directory.
|
*/

Route::get('/assets/{file}', function ($file) {
    $packageAssetsPath = __DIR__ . '/../public/vendor/analytics';
    $requestedFile = $packageAssetsPath . '/' . $file;

    // Security check - only allow specific file extensions and patterns
    $allowedExtensions = ['css', 'js', 'json', 'map'];
    $extension = pathinfo($file, PATHINFO_EXTENSION);

    if (!in_array($extension, $allowedExtensions)) {
        abort(404);
    }

    // Check if the exact file exists
    if (file_exists($requestedFile)) {
        $mimeType = match($extension) {
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'map' => 'application/json',
            default => 'text/plain'
        };

        return response()->file($requestedFile, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=31536000', // 1 year cache
        ]);
    }

    // If exact file doesn't exist, try to find hashed version
    $baseName = pathinfo($file, PATHINFO_FILENAME);
    $pattern = $packageAssetsPath . '/' . $baseName . '-*.' . $extension;
    $files = glob($pattern);

    if (!empty($files)) {
        $actualFile = $files[0]; // Get the first matching file
        $mimeType = match($extension) {
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'map' => 'application/json',
            default => 'text/plain'
        };

        return response()->file($actualFile, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=31536000', // 1 year cache
        ]);
    }

    abort(404);
})->where('file', '[a-zA-Z0-9._-]+\.(css|js|json|map)')
  ->name('analytics.assets');
