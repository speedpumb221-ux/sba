<?php

namespace App\Http\Controllers;

use App\Models\SpeedBump;
use App\Services\SpeedBumpService;
use App\Models\Report;
use App\Models\UserActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SpeedBumpController extends Controller
{
    public function index()
    {
        $bumps = SpeedBump::paginate(12);
        return view('bumps.index', compact('bumps'));
    }

    public function map()
    {
        $bumps = SpeedBump::all();
        return view('bumps.map', compact('bumps'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'description' => 'nullable|string|max:500',
            'type' => 'nullable|string',
        ]);

        // Use service to prevent duplicates within 20 meters
        $service = new SpeedBumpService();
        $bump = $service->findOrUpdateNearbyBump(
            $validated['latitude'],
            $validated['longitude'],
            20, // 20 meters radius
            [
                'description' => $validated['description'] ?? null,
                'type' => $validated['type'] ?? 'normal',
                'source' => 'user',
                'created_by' => Auth::id(),
            ]
        );

        if (Auth::check()) {
            UserActivity::create([
                'user_id' => Auth::id(),
                'activity_type' => 'added_bump',
                'subject_type' => SpeedBump::class,
                'subject_id' => $bump->id,
                'description' => 'أضاف مطب سرعة جديد',
            ]);
        }

        return response()->json([
            'success' => true,
            'bump' => $bump,
            'message' => 'تم إضافة المطب بنجاح',
        ]);
    }

    public function update(Request $request, SpeedBump $bump)
    {
        $validated = $request->validate([
            'description' => 'nullable|string|max:500',
            'type' => 'nullable|string',
        ]);

        $bump->update($validated);

        return response()->json([
            'success' => true,
            'bump' => $bump,
            'message' => 'تم تحديث المطب بنجاح',
        ]);
    }

    public function destroy(SpeedBump $bump)
    {
        $bump->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف المطب بنجاح',
        ]);
    }

    public function getNearby(Request $request)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'nullable|numeric|min:10|max:5000',
        ]);

        $radius = $validated['radius'] ?? 500; // meters
        $lat = $validated['latitude'];
        $lng = $validated['longitude'];

        // Use optimized query with scope and deduplicate by proximity
        $bumps = SpeedBump::nearLocation($lat, $lng, $radius)->get();
        $nearby = [];

        foreach ($bumps as $bump) {
            $distance = $bump->getDistance($lat, $lng);
            if ($distance > $radius) continue;

            // map confidence to string level if available, fallback to numeric mapping
            $confLevel = $bump->confidence_level ?? (isset($bump->confidence) ? ($bump->confidence >= 80 ? 'high' : ($bump->confidence >= 60 ? 'medium' : 'low')) : 'medium');

            $item = [
                'id' => $bump->id,
                'latitude' => $bump->latitude,
                'longitude' => $bump->longitude,
                'description' => $bump->description,
                'distance' => $distance,
                'confidence' => $confLevel,
                'created_at' => optional($bump->created_at)->toDateTimeString(),
                'is_verified' => $bump->is_verified,
                'type' => $bump->type,
                'source' => $bump->source,
            ];

            // deduplicate: if an existing item is within 10 meters, merge by keeping higher confidence/is_verified
            $merged = false;
            foreach ($nearby as &$existing) {
                $d = $this->haversineDistance($existing['latitude'], $existing['longitude'], $item['latitude'], $item['longitude']);
                if ($d <= 10) {
                    // keep the most verified or highest confidence level
                    $priority = ['low' => 1, 'medium' => 2, 'high' => 3];
                    $existingPriority = $priority[$existing['confidence']] ?? 2;
                    $itemPriority = $priority[$item['confidence']] ?? 2;

                    if ($item['is_verified'] && !$existing['is_verified']) {
                        $existing = $item;
                    } elseif ($itemPriority > $existingPriority) {
                        $existing = $item;
                    }

                    $merged = true;
                    break;
                }
            }

            if (!$merged) {
                $nearby[] = $item;
            }
        }

        usort($nearby, function ($a, $b) {
            return $a['distance'] <=> $b['distance'];
        });

        return response()->json([
            'success' => true,
            'nearby' => $nearby,
            'count' => count($nearby),
        ]);
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
            
            // Only alert if within alert distance
            if ($distance <= $alertDistance) {
                $alerts[] = [
                    'id' => $bump->id,
                    'latitude' => $bump->latitude,
                    'longitude' => $bump->longitude,
                    'description' => $bump->description,
                    'distance' => $distance,
                    'confidence_level' => $bump->confidence_level,
                    'is_verified' => $bump->is_verified,
                    'type' => $bump->type,
                    'alert_type' => $bump->is_verified ? 'verified' : 'unverified',
                    'should_alert' => true,
                ];
            }
        }

        // Sort by distance
        usort($alerts, function ($a, $b) {
            return $a['distance'] <=> $b['distance'];
        });

        return response()->json([
            'success' => true,
            'alerts' => $alerts,
            'alert_count' => count($alerts),
            'has_alerts' => count($alerts) > 0,
        ]);
    }

    public function report(Request $request, SpeedBump $bump)
    {
        $validated = $request->validate([
            'report_type' => 'required|in:confirm,false_positive,removed,new,update',
            'description' => 'nullable|string|max:500',
            'confidence' => 'nullable|numeric|min:0|max:100',
        ]);

        $report = Report::create([
            'speed_bump_id' => $bump->id,
            'user_id' => Auth::id(),
            'report_type' => $validated['report_type'],
            'description' => $validated['description'] ?? null,
            'confidence' => $validated['confidence'] ?? 50,
        ]);

        // Update bump based on report
        if ($validated['report_type'] === 'confirm') {
            $bump->incrementConfidence(5);
        } elseif ($validated['report_type'] === 'false_positive') {
            $bump->decrementConfidence(10);
        } elseif ($validated['report_type'] === 'update') {
            $bump->incrementConfidence(2);
        }

        UserActivity::create([
            'user_id' => Auth::id(),
            'activity_type' => 'reported_bump',
            'subject_type' => SpeedBump::class,
            'subject_id' => $bump->id,
            'description' => "أرسل تقرير: {$validated['report_type']}",
        ]);

        return response()->json([
            'success' => true,
            'report' => $report,
            'message' => 'تم إرسال التقرير بنجاح',
        ]);
    }

    public function getAll()
    {
        $bumps = SpeedBump::select(
            'id',
            'latitude',
            'longitude',
            'source',
            'confidence',
            'confidence_level',
            'is_verified',
            'description',
            'type',
            'reports_count'
        )->get();

        return response()->json([
            'success' => true,
            'bumps' => $bumps,
            'count' => $bumps->count(),
        ]);
    }

    public function getVerified()
    {
        $bumps = SpeedBump::verified()->get();

        return response()->json([
            'success' => true,
            'bumps' => $bumps,
            'count' => $bumps->count(),
        ]);
    }

    public function getHighConfidence()
    {
        $bumps = SpeedBump::highConfidence()->get();

        return response()->json([
            'success' => true,
            'bumps' => $bumps,
            'count' => $bumps->count(),
        ]);
    }

    public function batchReport(Request $request)
    {
        $validated = $request->validate([
            'reports' => 'required|array',
            'reports.*.bump_id' => 'required|exists:speed_bumps,id',
            'reports.*.report_type' => 'required|in:confirm,false_positive,removed,new,update',
            'reports.*.description' => 'nullable|string|max:500',
        ]);

        $reports = [];
        foreach ($validated['reports'] as $reportData) {
            $bump = SpeedBump::find($reportData['bump_id']);
            
            $report = Report::create([
                'speed_bump_id' => $bump->id,
                'user_id' => Auth::id(),
                'report_type' => $reportData['report_type'],
                'description' => $reportData['description'] ?? null,
            ]);

            // Update bump
            if ($reportData['report_type'] === 'confirm') {
                $bump->incrementConfidence(5);
            } elseif ($reportData['report_type'] === 'false_positive') {
                $bump->decrementConfidence(10);
            }

            $reports[] = $report;
        }

        UserActivity::create([
            'user_id' => Auth::id(),
            'activity_type' => 'batch_report',
            'description' => "أرسل " . count($reports) . " تقارير",
        ]);

        return response()->json([
            'success' => true,
            'reports' => $reports,
            'message' => 'تم إرسال التقارير بنجاح',
        ]);
    }
}
