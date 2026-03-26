<?php

namespace App\Services;

use App\Models\DeviceEvent;
use App\Models\SpeedBump;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class RoadEventAnalyzerService
{
    // thresholds and scoring
    public const VIBRATION_THRESHOLD = 12; // magnitude over which vibration counts
    public const VIBRATION_SCORE = 6;

    public const SPEED_DROP_THRESHOLD = 10; // km/h
    public const SPEED_DROP_SCORE = 4;

    public const REPEATED_USER_SCORE = 2; // repeated events by same user
    public const MULTI_USER_SCORE = 12; // events coming from multiple users

    // resulting confidence values (numeric for backward compatibility)
    public const CONFIDENCE_HIGH = 90;
    public const CONFIDENCE_MEDIUM = 65;
    // confidence level strings
    public const CONF_LEVEL_HIGH = 'high';
    public const CONF_LEVEL_MEDIUM = 'medium';
    public const CONF_LEVEL_LOW = 'low';

    protected SpeedBumpService $bumpService;

    public function __construct()
    {
        $this->bumpService = new SpeedBumpService();
    }

    /**
     * Analyze device events around a given location and create/update bumps if score threshold met.
     *
     * @param float $latitude
     * @param float $longitude
     * @param int $radius meters (default 20)
     * @param int $lookbackHours optional time window to consider events (default 24h)
     * @return array result with score and action performed
     */
    public function analyzeLocation(float $latitude, float $longitude, int $radius = 20, int $lookbackHours = 24): array
    {
        // 1) fetch candidate events within bounding box
        $lat_offset = $radius / 111000;
        $lon_offset = $radius / (111000 * cos(deg2rad($latitude)));

        $since = now()->subHours($lookbackHours);

        $events = DeviceEvent::whereBetween('latitude', [$latitude - $lat_offset, $latitude + $lat_offset])
            ->whereBetween('longitude', [$longitude - $lon_offset, $longitude + $lon_offset])
            ->where('is_processed', false)
            ->where('created_at', '>=', $since)
            ->orderBy('user_id')
            ->orderBy('created_at')
            ->get();

        if ($events->isEmpty()) {
            return ['score' => 0, 'action' => 'no_events', 'events_count' => 0];
        }

        // 2) filter precisely by haversine distance
        $events = $events->filter(function ($event) use ($latitude, $longitude, $radius) {
            $d = $this->haversineDistance($event->latitude, $event->longitude, $latitude, $longitude);
            return $d <= $radius;
        });

        if ($events->isEmpty()) {
            return ['score' => 0, 'action' => 'no_events_within_radius', 'events_count' => 0];
        }

        // 3) aggregate per user and compute score
        $byUser = $events->groupBy('user_id');
        $uniqueUsers = $byUser->keys()->filter()->values(); // drop null user ids

        $score = 0;

        // multi-user bonus
        if ($uniqueUsers->count() > 1) {
            $score += self::MULTI_USER_SCORE;
        }

        foreach ($byUser as $userId => $userEvents) {
            // compute per-user contribution
            $userEvents = $userEvents->values();

            // repeated events from same user
            if ($userEvents->count() > 1) {
                $score += self::REPEATED_USER_SCORE;
            }

            // vibration: if any event exceeds threshold -> +6 (once per user)
            $hasVibration = $userEvents->first(function ($e) {
                return ($e->vibration_magnitude ?? 0) > self::VIBRATION_THRESHOLD;
            });
            if ($hasVibration) {
                $score += self::VIBRATION_SCORE;
            }

            // sudden speed drop detection: check consecutive events for speed drop > threshold
            $prevSpeed = null;
            $speedDropDetected = false;
            foreach ($userEvents as $e) {
                if (!is_null($prevSpeed) && !is_null($e->speed)) {
                    $drop = $prevSpeed - $e->speed;
                    if ($drop > self::SPEED_DROP_THRESHOLD) {
                        $speedDropDetected = true;
                        break;
                    }
                }
                $prevSpeed = $e->speed;
            }
            if ($speedDropDetected) {
                $score += self::SPEED_DROP_SCORE;
            }
        }

        // 4) Decide action based on score
        $action = 'none';
        $createdBump = null;

        if ($score >= 20) {
            // create or update verified predicted bump
            $createdBump = $this->bumpService->findOrUpdateNearbyBump($latitude, $longitude, $radius, [
                'source' => 'predicted',
                'confidence' => self::CONFIDENCE_HIGH,
                'confidence_level' => self::CONF_LEVEL_HIGH,
                'is_verified' => true,
                'description' => 'AI-predicted bump',
            ]);
            $action = 'create_verified_bump';
        } elseif ($score >= 10) {
            // create or update possible bump with medium confidence
            $createdBump = $this->bumpService->findOrUpdateNearbyBump($latitude, $longitude, $radius, [
                'source' => 'predicted',
                'confidence' => self::CONFIDENCE_MEDIUM,
                'confidence_level' => self::CONF_LEVEL_MEDIUM,
                'is_verified' => false,
                'description' => 'AI-predicted possible bump',
            ]);
            $action = 'create_possible_bump';
        } else {
            $action = 'score_below_threshold';
        }

        // 5) mark events as processed (only those we used)
        try {
            $events->each(function ($e) use ($createdBump) {
                $e->is_processed = true;
                if ($createdBump) {
                    // optionally link event to bump (if column exists)
                    if (isset($e->speed_bump_id)) {
                        $e->speed_bump_id = $createdBump->id;
                    }
                }
                $e->save();
            });
        } catch (\Exception $ex) {
            Log::warning('Failed to mark events processed: ' . $ex->getMessage());
        }

        return [
            'score' => $score,
            'action' => $action,
            'events_count' => $events->count(),
            'bump' => $createdBump,
        ];
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
}
