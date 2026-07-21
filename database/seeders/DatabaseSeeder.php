<?php

namespace Database\Seeders;

use App\Models\BusSchedule;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AcademicCatalogSeeder::class,
            DemoContentSeeder::class,
        ]);

        if (!BusSchedule::where('bus_number', 'BUS-001')->exists()) {
            BusSchedule::create([
                'route_name' => 'Uttara to DSC',
                'departure_time' => '07:00',
                'departure_location' => 'Uttara',
                'arrival_location' => 'DSC',
                'bus_number' => 'BUS-001',
                'price' => 50.00,
                'run_days' => ['sun', 'mon', 'tue', 'wed', 'thu'],
                'schedule_note' => 'Morning corridor — board early during exams.',
                'university_tags' => ['DIU', 'DSC'],
                'is_active' => true,
                'current_lat' => 23.8103,
                'current_lng' => 90.4125,
                'status' => 'on_time',
                'delay_minutes' => 0,
            ]);
        }

        // No Faker in production (composer --no-dev); use explicit attributes.
        if (!User::where('email', 'test@example.com')->exists()) {
            User::create([
                'name' => 'Test User',
                'email' => 'test@example.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]);
        }
    }
}
