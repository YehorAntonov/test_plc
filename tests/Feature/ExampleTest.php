<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    //TEST DATA
    private function createVehicle(array $overrides = []): int
    {
        return DB::table('vehicles')->insertGetId(array_merge([
            'make' => 'Toyota',
            'model' => 'Camry',
            'year' => 2020,
            'price' => 25000,
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));
    }

    //FEATURE TEST 1 (view counter operation)
    public function test_vehicle_show_endpoint_increments_views(): void
    {
        $vehicleId = $this->createVehicle();

        $this->getJson("/api/vehicles/{$vehicleId}")
            ->assertOk();

        $views = DB::table('vehicle_views')
            ->where('vehicle_id', $vehicleId)
            ->sum('views_count');

        $this->assertEquals(1, $views);
    }

    //FEATURE TEST 2 (work sorting records by views)
    public function test_trending_endpoint_returns_sorted_vehicles(): void
    {
        $vehicleA = $this->createVehicle(['id' => 1]);
        $vehicleB = $this->createVehicle(['id' => 2]);

        DB::table('vehicle_views')->insert([
            [
                'vehicle_id' => $vehicleA,
                'bucket_start' => now()->startOfMinute(),
                'views_count' => 5,
            ],
            [
                'vehicle_id' => $vehicleB,
                'bucket_start' => now()->startOfMinute(),
                'views_count' => 10,
            ],
        ]);

        $response = $this->getJson('/api/vehicles/trending');

        $response
            ->assertOk()
            ->assertJsonPath('0.id', $vehicleB)
            ->assertJsonPath('0.views_count', 10)
            ->assertJsonPath('1.id', $vehicleA)
            ->assertJsonPath('1.views_count', 5);
    }

    //FEATURE TEST 3 (work sorting records by views if views are equal)
    public function test_trending_endpoint_applies_tie_break_by_vehicle_id(): void
    {
        $vehicleA = $this->createVehicle(['id' => 1]);
        $vehicleB = $this->createVehicle(['id' => 2]);

        DB::table('vehicle_views')->insert([
            [
                'vehicle_id' => $vehicleA,
                'bucket_start' => now()->startOfMinute(),
                'views_count' => 10,
            ],
            [
                'vehicle_id' => $vehicleB,
                'bucket_start' => now()->startOfMinute(),
                'views_count' => 10,
            ],
        ]);

        $response = $this->getJson('/api/vehicles/trending');

        $response
            ->assertOk()
            ->assertJsonPath('0.id', 1)
            ->assertJsonPath('1.id', 2);
    }

    //FEATURE TEST 4 (work sorting records by views only for the last 24 hours)
    public function test_trending_endpoint_ignores_views_older_than_24_hours(): void
    {
        $vehicleId = $this->createVehicle();

        DB::table('vehicle_views')->insert([
            [
                'vehicle_id' => $vehicleId,
                'bucket_start' => now()->subDays(2),
                'views_count' => 100,
            ],
        ]);

        $response = $this->getJson('/api/vehicles/trending');

        $response
            ->assertOk()
            ->assertJsonMissing([
                'id' => $vehicleId,
            ]);
    }

    //FEATURE TEST 5 (work of displaying the top 10 records by views)
    public function test_trending_endpoint_returns_only_10_vehicles(): void
    {
        for ($i = 1; $i <= 15; $i++) {
            $vehicleId = $this->createVehicle(['id' => $i]);

            DB::table('vehicle_views')->insert([
                [
                    'vehicle_id' => $vehicleId,
                    'bucket_start' => now()->startOfMinute(),
                    'views_count' => $i,
                ],
            ]);
        }

        $response = $this->getJson('/api/vehicles/trending');

        $response->assertOk();

        $this->assertCount(10, $response->json());
    }
}