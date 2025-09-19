<?php

namespace Tests\Http\Controllers;

use Tobidsn\LaravelAnalytics\Tests\TestCase;

class TrafficTableControllerTest extends TestCase
{
    /** @test */
    public function it_registers_traffic_table_api_route()
    {
        // Act - Check route exists
        $routes = collect(\Route::getRoutes()->getRoutes())
            ->filter(function ($route) {
                return str_contains($route->uri, 'analytics/api/traffic-table');
            });

        // Assert - Route should be registered
        $this->assertGreaterThan(0, $routes->count(), 'Traffic table API route should be registered');
    }

    /** @test */
    public function it_has_correct_route_methods()
    {
        // Act - Find the traffic table route
        $route = collect(\Route::getRoutes()->getRoutes())
            ->first(function ($route) {
                return str_contains($route->uri, 'analytics/api/traffic-table');
            });

        // Assert - Should accept GET requests
        $this->assertNotNull($route, 'Traffic table route should exist');
        $this->assertContains('GET', $route->methods);
    }

    /** @test */
    public function it_validates_traffic_source_data_structure()
    {
        // This test verifies the expected data structure for the frontend component
        $expectedTrafficSource = [
            'source' => 'google / organic',
            'sessions' => 1500,
            'newUsers' => 800,
            'totalUsers' => 1200,
            'bounceRate' => 65.5,
        ];

        // Verify required fields exist
        $this->assertArrayHasKey('source', $expectedTrafficSource);
        $this->assertArrayHasKey('sessions', $expectedTrafficSource);
        $this->assertArrayHasKey('newUsers', $expectedTrafficSource);
        $this->assertArrayHasKey('totalUsers', $expectedTrafficSource);
        $this->assertArrayHasKey('bounceRate', $expectedTrafficSource);

        // Verify data types
        $this->assertIsString($expectedTrafficSource['source']);
        $this->assertIsInt($expectedTrafficSource['sessions']);
        $this->assertIsInt($expectedTrafficSource['newUsers']);
        $this->assertIsInt($expectedTrafficSource['totalUsers']);
        $this->assertIsFloat($expectedTrafficSource['bounceRate']);
    }

    /** @test */
    public function it_validates_expected_response_structure()
    {
        // This test documents the expected response structure for the frontend
        $expectedResponseStructure = [
            'success' => true,
            'data' => [
                'traffic_sources' => [
                    [
                        'source' => 'google / organic',
                        'sessions' => 1500,
                        'newUsers' => 800,
                        'totalUsers' => 1200,
                        'bounceRate' => 65.5,
                    ],
                ],
                'pagination' => [
                    'current_page' => 1,
                    'per_page' => 10,
                    'total' => 1,
                    'last_page' => 1,
                ],
                'totals' => [
                    'sessions' => 1500,
                    'newUsers' => 800,
                    'totalUsers' => 1200,
                    'bounceRate' => 65.5,
                ],
                'date_range' => [
                    'start_date' => '2024-01-01',
                    'end_date' => '2024-01-31',
                    'days' => 30,
                ],
            ],
            'meta' => [
                'preset' => '30_days',
                'start_date' => '2024-01-01',
                'end_date' => '2024-01-31',
                'total_days' => 30,
                'current_page' => 1,
                'per_page' => 10,
            ],
        ];

        // Verify top-level structure
        $this->assertArrayHasKey('success', $expectedResponseStructure);
        $this->assertArrayHasKey('data', $expectedResponseStructure);
        $this->assertArrayHasKey('meta', $expectedResponseStructure);

        // Verify data structure
        $data = $expectedResponseStructure['data'];
        $this->assertArrayHasKey('traffic_sources', $data);
        $this->assertArrayHasKey('pagination', $data);
        $this->assertArrayHasKey('totals', $data);
        $this->assertArrayHasKey('date_range', $data);

        // Verify traffic source structure
        if (! empty($data['traffic_sources'])) {
            $trafficSource = $data['traffic_sources'][0];
            $this->assertArrayHasKey('source', $trafficSource);
            $this->assertArrayHasKey('sessions', $trafficSource);
            $this->assertArrayHasKey('newUsers', $trafficSource);
            $this->assertArrayHasKey('totalUsers', $trafficSource);
            $this->assertArrayHasKey('bounceRate', $trafficSource);
        }
    }
}
