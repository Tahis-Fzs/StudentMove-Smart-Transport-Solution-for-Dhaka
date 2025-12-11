<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\BusSchedule;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        BusSchedule::create([
            'route_name' => 'Uttara to DSC',
            'departure_time' => '07:00',
            'departure_location' => 'Rajlakshmi',
            'arrival_location' => 'DSC',
            'bus_number' => 'BUS-001',
            'price' => 50.00,
            'is_active' => true,
            'current_lat' => 23.8103,
            'current_lng' => 90.4125,
            'status' => 'on_time',
            'delay_minutes' => 0,
        ]);
    }
}
