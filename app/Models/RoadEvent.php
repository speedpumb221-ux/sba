<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoadEvent extends Model
{
    use HasFactory;

    protected $table = 'road_events';

    protected $fillable = [
        'user_id',
        'latitude',
        'longitude',
        'speed',
        'vibration',
        'is_processed',
        'speed_bump_id',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'speed' => 'decimal:2',
        'vibration' => 'decimal:4',
        'is_processed' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function speedBump()
    {
        return $this->belongsTo(SpeedBump::class, 'speed_bump_id');
    }
}
