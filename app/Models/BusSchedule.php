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
        'current_lat',     // GPS Latitude (double/float)
        'current_lng',     // GPS Longitude (double/float)
        'heading',         // Direction the bus is facing (e.g., degrees or string)
        // Optionally retain any other tracking/delay fields, if needed
    ];
}