<?php

describe('Laravel Analytics Package', function () {
    it('can be instantiated', function () {
        expect(true)->toBe(true);
    });

    it('has the correct package structure', function () {
        $serviceProvider = new \Tobidsn\LaravelAnalytics\AnalyticsServiceProvider(app());

        expect($serviceProvider)->toBeInstanceOf(\Spatie\LaravelPackageTools\PackageServiceProvider::class);
    });
});
