<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SpeedBump extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'latitude',
        'longitude',
        'source',
        'confidence_level',
        'report_count',
        'is_verified',
        'description',
        'user_id',
        'type',
        'score',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_verified' => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    public function prediction()
    {
        return $this->hasOne(Prediction::class, 'converted_to_bump_id');
    }

    public function incrementConfidence()
    {
        $this->increment('confidence', 5);
        $this->increment('reports_count');
        
        if ($this->confidence >= 80) {
            $this->update(['is_verified' => true]);
        }
    }

    public function decrementConfidence()
    {
        $this->decrement('confidence', 10);
        
        if ($this->confidence <= 20) {
            $this->delete();
        }
    }

    public function getDistance($lat, $lng)
    {
        return $this->haversineDistance($this->latitude, $this->longitude, $lat, $lng);
    }

    private function haversineDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earth_radius = 6371000; // meters

        $lat1_rad = deg2rad($lat1);
        $lat2_rad = deg2rad($lat2);
        $delta_lat = deg2rad($lat2 - $lat1);
        $delta_lon = deg2rad($lon2 - $lon1);

        $a = sin($delta_lat / 2) * sin($delta_lat / 2) +
             cos($lat1_rad) * cos($lat2_rad) * sin($delta_lon / 2) * sin($delta_lon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earth_radius * $c;

        return round($distance, 2);
    }

    public function isNearby($latitude, $longitude, $radius = 100)
    {
        return $this->getDistance($latitude, $longitude) <= $radius;
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeUnverified($query)
    {
        return $query->where('is_verified', false);
    }

    public function scopeHighConfidence($query)
    {
        return $query->where('confidence_level', '>=', 70);
    }

    public function scopeNearLocation($query, $latitude, $longitude, $radius = 1000)
    {
        $lat_offset = $radius / 111000;
        $lon_offset = $radius / (111000 * cos(deg2rad($latitude)));

        return $query->whereBetween('latitude', [$latitude - $lat_offset, $latitude + $lat_offset])
                     ->whereBetween('longitude', [$longitude - $lon_offset, $longitude + $lon_offset]);
    }
}

