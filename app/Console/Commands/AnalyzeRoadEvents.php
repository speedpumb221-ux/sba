<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RoadEvent;
use App\Services\SpeedBumpService;
use Illuminate\Support\Facades\Log;

class AnalyzeRoadEvents extends Command
{
    protected $signature = 'road-events:analyze';
    protected $description = 'Analyze new road events, score clusters and create predicted speed bumps';

    // scoring constants (keep in sync with RoadEventAnalyzerService)
    private const VIBRATION_THRESHOLD = 12;
    private const VIBRATION_SCORE = 6;
    private const SPEED_DROP_THRESHOLD = 10;
    private const SPEED_DROP_SCORE = 4;
    private const REPEATED_USER_SCORE = 2;
    private const MULTI_USER_SCORE = 12;

    private const RADIUS_METERS = 20; // cluster radius

    public function handle()
    {
        $this->info('Starting road-events analysis...');

        $events = RoadEvent::where('is_processed', false)->orderBy('created_at')->get();

        if ($events->isEmpty()) {
            $this->info('No new events.');
            return 0;
        }

        $service = new SpeedBumpService();

        // Convert to array we can mutate
        $pool = $events->values();

        while ($pool->isNotEmpty()) {
            $seed = $pool->shift();

            // build cluster: events within RADIUS_METERS of seed
            $cluster = $pool->filter(function ($e) use ($seed) {
                return $this->haversineDistance($seed->latitude, $seed->longitude, $e->latitude, $e->longitude) <= self::RADIUS_METERS;
            })->values();

            // include seed
            $cluster->prepend($seed);

            // remove clustered events from pool
            $pool = $pool->reject(function ($e) use ($cluster) {
                foreach ($cluster as $c) {
                    if ($c->id === $e->id) return true;
                }
                return false;
            })->values();

            // compute score for this cluster
            $score = $this->scoreCluster($cluster);

            $this->info('Cluster at ' . $seed->latitude . ',' . $seed->longitude . ' score=' . $score . ' events=' . $cluster->count());

            $bump = null;
            if ($score >= 20) {
                $bump = $service->findOrUpdateNearbyBump($seed->latitude, $seed->longitude, self::RADIUS_METERS, [
                    'source' => 'predicted',
                    'confidence' => 90,
                    'confidence_level' => 'high',
                    'is_verified' => true,
                    'description' => 'Auto-created from road events',
                ]);
            } elseif ($score >= 10) {
                $bump = $service->findOrUpdateNearbyBump($seed->latitude, $seed->longitude, self::RADIUS_METERS, [
                    'source' => 'predicted',
                    'confidence' => 65,
                    'confidence_level' => 'medium',
                    'is_verified' => false,
                    'description' => 'Auto-created (possible) from road events',
                ]);
            }

            // mark events processed and link to bump if created
            foreach ($cluster as $ev) {
                $ev->is_processed = true;
                if ($bump) $ev->speed_bump_id = $bump->id;
                $ev->save();
            }
        }

        $this->info('Analysis complete.');
        return 0;
    }

    private function scoreCluster($cluster)
    {
        $byUser = $cluster->groupBy('user_id');
        $uniqueUsers = $byUser->keys()->filter()->values();

        $score = 0;
        if ($uniqueUsers->count() > 1) $score += self::MULTI_USER_SCORE;

        foreach ($byUser as $userId => $userEvents) {
            $userEvents = $userEvents->sortBy('created_at')->values();

            if ($userEvents->count() > 1) $score += self::REPEATED_USER_SCORE;

            // vibration
            $hasVib = $userEvents->first(function ($e) { return ($e->vibration ?? 0) > self::VIBRATION_THRESHOLD; });
            if ($hasVib) $score += self::VIBRATION_SCORE;

            // speed drop detection
            $prev = null; $dropDetected = false;
            foreach ($userEvents as $e) {
                if (!is_null($prev) && !is_null($e->speed)) {
                    $drop = $prev - $e->speed;
                    if ($drop > self::SPEED_DROP_THRESHOLD) { $dropDetected = true; break; }
                }
                $prev = $e->speed;
            }
            if ($dropDetected) $score += self::SPEED_DROP_SCORE;
        }

        return $score;
    }

    private function haversineDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earth_radius = 6371000;
        $lat1_rad = deg2rad($lat1);
        $lat2_rad = deg2rad($lat2);
        $dlat = deg2rad($lat2 - $lat1);
        $dlon = deg2rad($lon2 - $lon1);
        $a = sin($dlat/2) * sin($dlat/2) + cos($lat1_rad) * cos($lat2_rad) * sin($dlon/2) * sin($dlon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return round($earth_radius * $c, 2);
    }
}
