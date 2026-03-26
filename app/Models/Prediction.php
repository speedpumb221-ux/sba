<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prediction extends Model
{
    use HasFactory;

    protected $fillable = [
        'latitude',
        'longitude',
        'score',
        'vibration_count',
        'speed_drop_count',
        'user_count',
        'is_converted',
        'converted_to_bump_id',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_converted' => 'boolean',
    ];

    public function convertedBump()
    {
        return $this->belongsTo(SpeedBump::class, 'converted_to_bump_id');
    }

    public function addScore($points)
    {
        $this->increment('score', $points);

        // Auto-convert to bump if score is high enough
        if ($this->score >= 15 && !$this->is_converted) {
            $this->convertToBump();
        }
    }

    public function convertToBump()
    {
        $bump = SpeedBump::create([
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'source' => 'predicted',
            'confidence' => min(100, $this->score * 5),
            'is_verified' => $this->score >= 20,
        ]);

        $this->update([
            'is_converted' => true,
            'converted_to_bump_id' => $bump->id,
        ]);

        return $bump;
    }

    public function getConfidenceLevel()
    {
        if ($this->score >= 15) {
            return 'confirmed';
        } elseif ($this->score >= 8) {
            return 'probable';
        }
        return 'possible';
    }
}
