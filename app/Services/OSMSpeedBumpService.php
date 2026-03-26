<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Arr;

class OSMSpeedBumpService
{
    /**
     * Query Overpass API for speed-bump features around a point and store them.
     *
     * @param float $latitude
     * @param float $longitude
     * @param int $radiusMeters
     * @return array
     */
    public function fetchAndStoreNearby(float $latitude, float $longitude, int $radiusMeters = 1000): array
    {
        $radius = (int) $radiusMeters;

        // Overpass QL: look for common speed bump / traffic_calming tags
        $query = "[out:json][timeout:25];\n(\n  node(around:$radius,$latitude,$longitude)[\"highway\"=\"speed_bump\"];\n  node(around:$radius,$latitude,$longitude)[\"traffic_calming\"=\"bump\"];\n  node(around:$radius,$latitude,$longitude)[\"traffic_calming\"=\"speed_bump\"];\n  way(around:$radius,$latitude,$longitude)[\"highway\"=\"speed_bump\"];\n  way(around:$radius,$latitude,$longitude)[\"traffic_calming\"=\"bump\"];\n  way(around:$radius,$latitude,$longitude)[\"traffic_calming\"=\"speed_bump\"];\n);\nout center;";

        $response = Http::asForm()->post('https://overpass-api.de/api/interpreter', ['data' => $query]);

        if (!$response->ok()) {
            return ['success' => false, 'status' => $response->status(), 'body' => $response->body()];
        }

        $json = $response->json();
        $elements = Arr::get($json, 'elements', []);

        $bumpService = new SpeedBumpService();
        $results = [];

        foreach ($elements as $el) {
            // determine coordinates
            if (isset($el['lat']) && isset($el['lon'])) {
                $lat = $el['lat'];
                $lon = $el['lon'];
            } elseif (isset($el['center']['lat']) && isset($el['center']['lon'])) {
                $lat = $el['center']['lat'];
                $lon = $el['center']['lon'];
            } else {
                continue;
            }

            $tags = Arr::get($el, 'tags', []);
            $descParts = [];
            if (!empty($tags)) {
                foreach ($tags as $k => $v) {
                    $descParts[] = "$k=$v";
                }
            }

            $description = implode('; ', $descParts) ?: null;

            // Use the existing SpeedBumpService to avoid duplicates (radius 20m)
            $bump = $bumpService->findOrUpdateNearbyBump($lat, $lon, 20, [
                'source' => 'osm',
                'confidence' => 65,
                'confidence_level' => 'medium',
                'is_verified' => false,
                'description' => $description,
            ]);

            $results[] = $bump;
        }

        return ['success' => true, 'count' => count($results), 'bumps' => $results];
    }
}
