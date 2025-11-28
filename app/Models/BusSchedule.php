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
        'is_active'
    ];
}