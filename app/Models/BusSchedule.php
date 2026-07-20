<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'route_name',
        'departure_time',
        'departure_location',
        'arrival_location',
        'bus_number',
        'price',
        'is_active',
        // 🚀 ADD THESE FOR DYNAMIC TRACKING
        'current_lat',
        'current_lng',
        'location_updated_at',
        'heading',
        'status',
        'delay_minutes',
    ];

    protected $casts = [
        'location_updated_at' => 'datetime',
        'is_active' => 'boolean',
    ];
}