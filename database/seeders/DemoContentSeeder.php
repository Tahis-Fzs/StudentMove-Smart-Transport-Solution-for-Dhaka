<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\Offer;
use Illuminate\Database\Seeder;

class DemoContentSeeder extends Seeder
{
    public function run(): void
    {
        $studentPass = Offer::updateOrCreate(
            ['title' => 'Student Pass — 20% off monthly'],
            [
                'description' => 'Valid on monthly StudentMove passes for registered students.',
                'discount_percentage' => 20,
                'valid_from' => now()->subDay(),
                'valid_until' => now()->addMonths(3),
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        Offer::updateOrCreate(
            ['title' => 'Exam Week Express'],
            [
                'description' => '15% off weekly passes during exam season.',
                'discount_percentage' => 15,
                'valid_from' => now()->subDay(),
                'valid_until' => now()->addMonths(2),
                'is_active' => true,
                'sort_order' => 2,
            ]
        );

        $announcements = [
            [
                'title' => 'Welcome to StudentMove',
                'message' => 'Complete your profile to unlock route suggestions and student offers.',
                'icon' => 'bi-star',
                'icon_color' => 'green',
                'type' => 'success',
                'sort_order' => 1,
            ],
            [
                'title' => 'Live map updated',
                'message' => 'Driver GPS is active on corridor routes — check Live map for ETAs.',
                'icon' => 'bi-bus-front',
                'icon_color' => 'blue',
                'type' => 'info',
                'sort_order' => 2,
            ],
            [
                'title' => 'Student offer live',
                'message' => '20% off monthly passes this semester. See Plans for details.',
                'icon' => 'bi-gift',
                'icon_color' => 'red',
                'type' => 'info',
                'sort_order' => 3,
                'offer_id' => $studentPass->id,
            ],
            [
                'title' => 'Feedback welcome',
                'message' => 'Tell us how your commute went — we read every message.',
                'icon' => 'bi-chat-dots',
                'icon_color' => 'blue',
                'type' => 'info',
                'sort_order' => 4,
            ],
        ];

        foreach ($announcements as $row) {
            Notification::updateOrCreate(
                ['title' => $row['title']],
                array_merge($row, [
                    'audience' => Notification::AUDIENCE_ALL,
                    'is_active' => true,
                    'published_at' => now()->subHour(),
                    'expires_at' => now()->addMonths(6),
                ])
            );
        }
    }
}
