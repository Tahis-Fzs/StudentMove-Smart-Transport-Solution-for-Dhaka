<?php

namespace Database\Seeders;

use App\Models\Notification;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Notification::create([
            'message' => 'Your bus #7 is arriving in 5 minutes!',
            'icon' => 'bi-bus-front',
            'icon_color' => 'blue',
            'type' => 'info',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Notification::create([
            'message' => 'Your feedback was received. Thank you!',
            'icon' => 'bi-check-circle',
            'icon_color' => 'green',
            'type' => 'success',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        Notification::create([
            'message' => 'Special Offer: 30% off for students!',
            'icon' => 'bi-gift',
            'icon_color' => 'red',
            'type' => 'info',
            'is_active' => true,
            'sort_order' => 3,
        ]);

        Notification::create([
            'message' => 'Route #205 schedule updated for tomorrow',
            'icon' => 'bi-clock',
            'icon_color' => 'blue',
            'type' => 'info',
            'is_active' => true,
            'sort_order' => 4,
        ]);

        Notification::create([
            'message' => 'Welcome to StudentMove! Complete your profile to get started.',
            'icon' => 'bi-star',
            'icon_color' => 'green',
            'type' => 'success',
            'is_active' => true,
            'sort_order' => 5,
        ]);
    }
}

