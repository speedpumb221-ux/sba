<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'speed_bump_id',
        'user_id',
        'report_type',
        'comment',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function speedBump()
    {
        return $this->belongsTo(SpeedBump::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
