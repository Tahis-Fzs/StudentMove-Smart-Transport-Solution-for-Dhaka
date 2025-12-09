<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = ['admin_id', 'action', 'description'];

    // Link to the Admin (User) who did the action
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}