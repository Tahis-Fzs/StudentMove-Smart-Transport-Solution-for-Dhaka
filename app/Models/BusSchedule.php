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
        // 👇 NEW FIELDS FOR FR-11, FR-12, FR-15
        'current_lat',      // GPS Latitude
        'current_lng',      // GPS Longitude
        'delay_minutes',    // How late is the bus?
        'status'            // 'on_time', 'delayed', 'stopped'
    ];
}