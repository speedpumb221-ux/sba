<?php

namespace App\Http\Controllers;

use App\Models\RoadEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoadEventController extends Controller
{
    public function index(Request $request)
    {
        // optional: filter by user or bounding box
        $query = RoadEvent::query();

        if ($request->has('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        if ($request->has('since')) {
            $query->where('created_at', '>=', $request->input('since'));
        }

        $events = $query->orderBy('created_at', 'desc')->paginate(50);

        return response()->json(['success' => true, 'events' => $events]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'speed' => 'nullable|numeric',
            'vibration' => 'nullable|numeric',
        ]);

        $userId = $validated['user_id'] ?? Auth::id();

        $event = RoadEvent::create([
            'user_id' => $userId,
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'speed' => $validated['speed'] ?? null,
            'vibration' => $validated['vibration'] ?? null,
        ]);

        return response()->json(['success' => true, 'event' => $event], 201);
    }

    /**
     * Public ingest endpoint for devices that cannot authenticate.
     * Validates latitude, longitude, speed, vibration and stores the event.
     */
    public function storePublic(Request $request)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'speed' => 'nullable|numeric',
            'vibration' => 'nullable|numeric',
        ]);

        $event = RoadEvent::create([
            'user_id' => null,
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'speed' => $validated['speed'] ?? null,
            'vibration' => $validated['vibration'] ?? null,
        ]);

        return response()->json(['success' => true, 'event' => $event], 201);
    }

    public function show(RoadEvent $roadEvent)
    {
        return response()->json(['success' => true, 'event' => $roadEvent]);
    }

    public function destroy(RoadEvent $roadEvent)
    {
        $roadEvent->delete();
        return response()->json(['success' => true]);
    }
}
