<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\SpeedBump;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'speed_bump_id' => 'required|exists:speed_bumps,id',
            'report_type' => 'required|in:confirm,false_positive,removed,new',
            'comment' => 'nullable|string|max:500',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        $report = Report::create([
            'speed_bump_id' => $validated['speed_bump_id'],
            'user_id' => Auth::id(),
            'report_type' => $validated['report_type'],
            'comment' => $validated['comment'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
        ]);

        // Update bump confidence based on report
        $bump = SpeedBump::find($validated['speed_bump_id']);
        if ($bump) {
            if ($validated['report_type'] === 'confirm') {
                $bump->incrementConfidence();
            } elseif ($validated['report_type'] === 'false_positive') {
                $bump->decrementConfidence();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'تم إرسال التقرير بنجاح',
            'report' => $report,
        ]);
    }
}
