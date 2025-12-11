<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'message',
        'icon',
        'icon_color',
        'type',
        'is_active',
        'sort_order',
        'offer_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the offer associated with this notification
     */
    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }
}
