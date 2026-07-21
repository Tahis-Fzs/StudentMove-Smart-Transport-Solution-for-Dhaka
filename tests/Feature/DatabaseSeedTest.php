<?php

namespace Tests\Feature;

use App\Models\BusSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_demo_bus_without_faker(): void
    {
        $this->seed();

        $this->assertDatabaseHas('bus_schedules', [
            'bus_number' => 'BUS-001',
        ]);

        $this->assertGreaterThan(0, \App\Models\University::count());
    }

    public function test_public_gps_update_requires_driver_session(): void
    {
        $bus = BusSchedule::create([
            'route_name' => 'Test Route',
            'departure_time' => '07:00',
            'departure_location' => 'A',
            'arrival_location' => 'B',
            'bus_number' => 'BUS-TEST',
            'price' => 50,
            'is_active' => true,
        ]);

        $this->postJson('/api/bus/update-location', [
            'bus_id' => $bus->id,
            'lat' => 23.81,
            'lng' => 90.41,
        ])->assertStatus(302);
    }
}
