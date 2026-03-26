<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'latitude',
        'longitude',
        'speed',
        'acceleration_x',
        'acceleration_y',
        'acceleration_z',
        'vibration_magnitude',
        'is_processed',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_processed' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getVibrationLevel()
    {
        if (!$this->vibration_magnitude) {
            return 'none';
        }

        if ($this->vibration_magnitude > 15) {
            return 'strong';
        } elseif ($this->vibration_magnitude > 8) {
            return 'moderate';
        }
        return 'light';
    }

    public function isSpeedDrop($previousSpeed = null)
    {
        if (!$previousSpeed || !$this->speed) {
            return false;
        }

        $drop = $previousSpeed - $this->speed;
        return $drop > 10; // More than 10 km/h drop
    }
}
