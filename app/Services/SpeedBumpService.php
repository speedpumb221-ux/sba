<?php

namespace App\Services;

use App\Models\SpeedBump;

class SpeedBumpService
{
    /**
     * Find existing speed bump within radius or create new one
     * Prevents duplicate speed bumps within specified radius
     *
     * @param float $latitude
     * @param float $longitude
     * @param int $radius Radius in meters (default: 20)
     * @param array $attributes Additional attributes for the speed bump
     * @return SpeedBump
     */
    public function findOrUpdateNearbyBump($latitude, $longitude, $radius = 20, $attributes = [])
    {
        // First, get potential nearby bumps using bounding box
        $potentialBumps = SpeedBump::nearLocation($latitude, $longitude, $radius)->get();

        // Check each potential bump with precise distance calculation
        foreach ($potentialBumps as $bump) {
            if ($bump->getDistance($latitude, $longitude) <= $radius) {
                // Found nearby bump, update it instead of creating new one
                $updateData = [];

                // Increase confidence if not already high
                $currentConfidence = $bump->confidence ?? 50;
                if ($currentConfidence < 90) {
                    $updateData['confidence'] = min(100, $currentConfidence + 10);
                }

                // Update source if different and more reliable
                if (isset($attributes['source']) && $attributes['source'] !== $bump->source) {
                    $sourcePriority = ['user' => 4, 'osm' => 3, 'google' => 2, 'predicted' => 1];
                    $currentPriority = $sourcePriority[$bump->source] ?? 0;
                    $newPriority = $sourcePriority[$attributes['source']] ?? 0;

                    if ($newPriority > $currentPriority) {
                        $updateData['source'] = $attributes['source'];
                    }
                }

                // Update other attributes if provided and empty
                if (isset($attributes['description']) && empty($bump->description)) {
                    $updateData['description'] = $attributes['description'];
                }

                if (isset($attributes['type']) && empty($bump->type)) {
                    $updateData['type'] = $attributes['type'];
                }

                // Increment reports count
                $updateData['reports_count'] = ($bump->reports_count ?? 0) + 1;

                // Update the bump if there are changes
                if (!empty($updateData)) {
                    $bump->update($updateData);
                }

                return $bump;
            }
        }

        // No nearby bump found, create new one
        $defaultAttributes = [
            'latitude' => $latitude,
            'longitude' => $longitude,
            'source' => $attributes['source'] ?? 'user',
            // keep numeric confidence for compatibility, also allow confidence_level
            'confidence' => $attributes['confidence'] ?? 50,
            'confidence_level' => $attributes['confidence_level'] ?? ($this->mapNumericToLevel($attributes['confidence'] ?? 50)),
            'reports_count' => $attributes['reports_count'] ?? 1,
            'is_verified' => $attributes['is_verified'] ?? false,
            'description' => $attributes['description'] ?? null,
            'type' => $attributes['type'] ?? ($attributes['type'] ?? 'normal'),
            'created_by' => $attributes['created_by'] ?? null,
        ];

        $createData = array_merge($defaultAttributes, $attributes);

        return SpeedBump::create($createData);
    }

    /**
     * Check if a speed bump exists within the specified radius
     *
     * @param float $latitude
     * @param float $longitude
     * @param int $radius Radius in meters
     * @return SpeedBump|null
     */
    public function findNearbyBump($latitude, $longitude, $radius = 20)
    {
        $potentialBumps = SpeedBump::nearLocation($latitude, $longitude, $radius)->get();

        foreach ($potentialBumps as $bump) {
            if ($bump->getDistance($latitude, $longitude) <= $radius) {
                return $bump;
            }
        }

        return null;
    }

    /**
     * Get all speed bumps within a radius
     *
     * @param float $latitude
     * @param float $longitude
     * @param int $radius Radius in meters
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getNearbyBumps($latitude, $longitude, $radius = 100)
    {
        $bumps = SpeedBump::nearLocation($latitude, $longitude, $radius)->get();
        $nearby = collect();

        foreach ($bumps as $bump) {
            $distance = $bump->getDistance($latitude, $longitude);
            if ($distance <= $radius) {
                $bump->distance = $distance;
                $nearby->push($bump);
            }
        }

        return $nearby->sortBy('distance');
    }

    private function mapNumericToLevel($num)
    {
        $num = (int)$num;
        if ($num >= 80) return 'high';
        if ($num >= 60) return 'medium';
        return 'low';
    }
}