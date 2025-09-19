<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>Analytics Dashboard - {{ config('app.name', 'Laravel') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Styles -->
    <link href="@analyticsAsset('app.css')" rel="stylesheet">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
</head>
<body class="font-sans antialiased bg-gray-50 dark:bg-gray-900">
    <div class="min-h-screen">
        
        <!-- Navigation Header -->
        <nav class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <!-- Logo -->
                        <div class="flex-shrink-0">
                            <h1 class="text-xl font-semibold text-gray-900 dark:text-white">
                                📊 Analytics Dashboard
                            </h1>
                        </div>
                    </div>
                    
                    <!-- User menu -->
                    <div class="flex items-center space-x-4">
                        @auth
                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                {{ Auth::user()->name ?? 'User' }}
                            </span>
                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button type="submit" 
                                        class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                                    Logout
                                </button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" 
                               class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                                Login
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <!-- Page Header -->
                <div class="mb-8">
                    <div class="sm:flex sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-2xl font-bold leading-tight text-gray-900 dark:text-white">
                                Website Analytics
                            </h2>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                Monitor your website's performance and visitor insights
                            </p>
                        </div>
                        
                        <!-- Refresh Button -->
                        <div class="mt-4 sm:mt-0">
                            <button onclick="window.location.reload()" 
                                    class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Refresh Data
                            </button>
                        </div>
                    </div>
                </div>

                <!-- React Analytics Dashboard -->
                <div id="analytics-dashboard" 
                     data-csrf-token="{{ csrf_token() }}"
                     data-api-base="{{ url('/analytics/api') }}">
                    <!-- React component will mount here -->
                    <div class="flex items-center justify-center py-16">
                        <div class="text-center">
                            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
                            <p class="text-gray-600 dark:text-gray-400">Loading analytics dashboard...</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 mt-16">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <div class="text-center text-sm text-gray-600 dark:text-gray-400">
                    <p>
                        Powered by 
                        <a href="https://github.com/tobidsn/laravel-analytics" 
                           target="_blank" 
                           class="text-blue-600 dark:text-blue-400 hover:underline">
                            Laravel Analytics Package
                        </a>
                    </p>
                </div>
            </div>
        </footer>
    </div>

    <!-- Scripts -->
    <script src="@analyticsAsset('app.js')" defer></script>
    
    <!-- Error Handling -->
    <script>
        window.addEventListener('error', function(e) {
            console.error('Analytics Dashboard Error:', e.error);
            const dashboard = document.getElementById('analytics-dashboard');
            if (dashboard) {
                dashboard.innerHTML = `
                    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-6">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-red-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                            <div>
                                <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
                                    Dashboard Loading Error
                                </h3>
                                <p class="mt-1 text-sm text-red-700 dark:text-red-300">
                                    There was an error loading the analytics dashboard. Please refresh the page or contact support.
                                </p>
                            </div>
                        </div>
                    </div>
                `;
            }
        });
    </script>
</body>
</html>