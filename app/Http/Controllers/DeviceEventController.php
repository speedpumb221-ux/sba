<?php

namespace App\Http\Controllers;

use App\Models\DeviceEvent;
use App\Models\Prediction;
use App\Models\SpeedBump;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeviceEventController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'speed' => 'nullable|numeric|min:0',
            'acceleration_x' => 'nullable|numeric',
            'acceleration_y' => 'nullable|numeric',
            'acceleration_z' => 'nullable|numeric',
            'vibration_magnitude' => 'nullable|numeric|min:0',
        ]);

        $event = DeviceEvent::create([
            'user_id' => Auth::id(),
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'speed' => $validated['speed'] ?? null,
            'acceleration_x' => $validated['acceleration_x'] ?? null,
            'acceleration_y' => $validated['acceleration_y'] ?? null,
            'acceleration_z' => $validated['acceleration_z'] ?? null,
            'vibration_magnitude' => $validated['vibration_magnitude'] ?? null,
        ]);

        // Process event for predictions
        $this->processPrediction($event);

        return response()->json([
            'success' => true,
            'event' => $event,
        ]);
    }

    private function processPrediction(DeviceEvent $event)
    {
        $score = 0;
        $vibrationCount = 0;
        $speedDropCount = 0;

        // Check for strong vibrations
        if ($event->vibration_magnitude && $event->vibration_magnitude > 15) {
            $score += 5;
            $vibrationCount++;
        }

        // Check for speed drops
        if ($event->speed && $event->speed > 0) {
            $previousEvent = DeviceEvent::where('user_id', $event->user_id)
                ->where('id', '<', $event->id)
                ->orderBy('id', 'desc')
                ->first();

            if ($previousEvent && $previousEvent->speed && ($previousEvent->speed - $event->speed) > 10) {
                $score += 3;
                $speedDropCount++;
            }
        }

        // Check for similar events from same user
        $similarEvents = DeviceEvent::where('user_id', $event->user_id)
            ->where('id', '<', $event->id)
            ->where('latitude', '>=', $event->latitude - 0.001)
            ->where('latitude', '<=', $event->latitude + 0.001)
            ->where('longitude', '>=', $event->longitude - 0.001)
            ->where('longitude', '<=', $event->longitude + 0.001)
            ->count();

        if ($similarEvents > 0) {
            $score += 2;
        }

        // Check for similar events from other users
        $otherUserEvents = DeviceEvent::where('user_id', '!=', $event->user_id)
            ->where('latitude', '>=', $event->latitude - 0.001)
            ->where('latitude', '<=', $event->latitude + 0.001)
            ->where('longitude', '>=', $event->longitude - 0.001)
            ->where('longitude', '<=', $event->longitude + 0.001)
            ->count();

        if ($otherUserEvents > 0) {
            $score += 10;
        }

        // Create or update prediction
        $prediction = Prediction::where('latitude', '>=', $event->latitude - 0.001)
            ->where('latitude', '<=', $event->latitude + 0.001)
            ->where('longitude', '>=', $event->longitude - 0.001)
            ->where('longitude', '<=', $event->longitude + 0.001)
            ->where('is_converted', false)
            ->first();

        if ($prediction) {
            $prediction->score += $score;
            $prediction->vibration_count += $vibrationCount;
            $prediction->speed_drop_count += $speedDropCount;
            $prediction->user_count++;
            $prediction->save();
        } else {
            $prediction = Prediction::create([
                'latitude' => $event->latitude,
                'longitude' => $event->longitude,
                'score' => $score,
                'vibration_count' => $vibrationCount,
                'speed_drop_count' => $speedDropCount,
                'user_count' => 1,
                'is_converted' => false,
            ]);
        }

        // Convert to bump if score is high enough
        if ($prediction->score >= 15 && !$prediction->is_converted) {
            $this->convertPredictionToBump($prediction);
        }

        $event->is_processed = true;
        $event->save();
    }

    private function convertPredictionToBump(Prediction $prediction)
    {
        // Check if bump already exists
        $existingBump = SpeedBump::where('latitude', '>=', $prediction->latitude - 0.001)
            ->where('latitude', '<=', $prediction->latitude + 0.001)
            ->where('longitude', '>=', $prediction->longitude - 0.001)
            ->where('longitude', '<=', $prediction->longitude + 0.001)
            ->first();

        if ($existingBump) {
            $existingBump->incrementConfidence(10);
        } else {
            SpeedBump::create([
                'latitude' => $prediction->latitude,
                'longitude' => $prediction->longitude,
                'source' => 'Predicted',
                'confidence_level' => 60,
                'report_count' => $prediction->user_count,
                'is_verified' => false,
                'score' => $prediction->score,
                'description' => "تنبؤ ذكي - درجة: {$prediction->score}",
            ]);
        }

        $prediction->is_converted = true;
        $prediction->save();
    }

    public function checkAlerts(Request $request)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'alert_distance' => 'nullable|numeric|min:10|max:1000',
        ]);

        $alertDistance = $validated['alert_distance'] ?? 100;
        $lat = $validated['latitude'];
        $lng = $validated['longitude'];

        // Get nearby bumps
        $bumps = SpeedBump::nearLocation($lat, $lng, $alertDistance)->get();
        $alerts = [];

        foreach ($bumps as $bump) {
            $distance = $bump->getDistance($lat, $lng);
            
            if ($distance <= $alertDistance) {
                $alerts[] = [
                    'id' => $bump->id,
                    'distance' => $distance,
                    'confidence_level' => $bump->confidence_level,
                    'is_verified' => $bump->is_verified,
                    'type' => $bump->type,
                    'description' => $bump->description,
                    'alert_level' => $this->getAlertLevel($distance, $bump->confidence_level),
                ];
            }
        }

        usort($alerts, function ($a, $b) {
            return $a['distance'] <=> $b['distance'];
        });

        return response()->json([
            'success' => true,
            'alerts' => $alerts,
            'alert_count' => count($alerts),
            'has_critical_alert' => $this->hasCriticalAlert($alerts),
        ]);
    }

    private function getAlertLevel($distance, $confidence)
    {
        if ($distance < 50 && $confidence >= 80) {
            return 'critical';
        } elseif ($distance < 100 && $confidence >= 60) {
            return 'high';
        } elseif ($distance < 200) {
            return 'medium';
        }
        return 'low';
    }

    private function hasCriticalAlert($alerts)
    {
        foreach ($alerts as $alert) {
            if ($alert['alert_level'] === 'critical') {
                return true;
            }
        }
        return false;
    }

    public function getPredictions()
    {
        $predictions = Prediction::where('is_converted', false)
            ->orderBy('score', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'predictions' => $predictions,
            'count' => $predictions->count(),
        ]);
    }

    public function batch(Request $request)
    {
        $validated = $request->validate([
            'events' => 'required|array',
            'events.*.latitude' => 'required|numeric|between:-90,90',
            'events.*.longitude' => 'required|numeric|between:-180,180',
            'events.*.speed' => 'nullable|numeric|min:0',
            'events.*.vibration_magnitude' => 'nullable|numeric|min:0',
        ]);

        $events = [];
        foreach ($validated['events'] as $eventData) {
            $event = DeviceEvent::create([
                'user_id' => Auth::id(),
                'latitude' => $eventData['latitude'],
                'longitude' => $eventData['longitude'],
                'speed' => $eventData['speed'] ?? null,
                'vibration_magnitude' => $eventData['vibration_magnitude'] ?? null,
            ]);

            $this->processPrediction($event);
            $events[] = $event;
        }

        return response()->json([
            'success' => true,
            'count' => count($events),
            'events' => $events,
        ]);
    }
}
