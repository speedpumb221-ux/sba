<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'language',
        'theme',
        'alert_distance',
        'notifications_enabled',
        'sound_enabled',
        'gps_enabled',
        'motion_tracking_enabled',
    ];

    protected $casts = [
        'notifications_enabled' => 'boolean',
        'sound_enabled' => 'boolean',
        'gps_enabled' => 'boolean',
        'motion_tracking_enabled' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
