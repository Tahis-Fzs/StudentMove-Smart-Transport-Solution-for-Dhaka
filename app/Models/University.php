<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class University extends Model
{
    protected $fillable = ['name', 'short_name', 'calendar_type', 'is_custom'];

    protected $casts = [
        'is_custom' => 'boolean',
    ];
}
