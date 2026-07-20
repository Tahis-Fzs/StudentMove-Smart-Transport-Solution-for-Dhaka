<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavedRoute extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'origin',
        'destination',
        'title',
        'duration_label',
        'cost_label',
        'transfers',
        'buses',
        'description',
        'comfort',
        'rating',
    ];

    protected $casts = [
        'buses' => 'array',
        'rating' => 'float',
        'transfers' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pathLabel(): string
    {
        return $this->origin . ' → ' . $this->destination;
    }
}
