<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    /**
     * Store or update user settings
     */
    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $allowed = [
            'language', 'theme', 'alert_distance',
            'notifications_enabled', 'sound_enabled', 'gps_enabled', 'motion_tracking_enabled'
        ];

        $data = [];
        foreach ($allowed as $key) {
            if ($request->has($key)) {
                $data[$key] = $request->input($key);
            }
        }

        if (empty($data)) {
            return response()->json(['success' => false, 'message' => 'No valid settings provided'], 400);
        }

        // Normalize booleans
        foreach (['notifications_enabled','sound_enabled','gps_enabled','motion_tracking_enabled'] as $b) {
            if (array_key_exists($b, $data)) {
                $val = $data[$b];
                if ($val === '0' || $val === 0 || $val === 'false' || $val === false) {
                    $data[$b] = false;
                } else {
                    $data[$b] = (bool) $val;
                }
            }
        }

        // Persist via relationship
        $settings = $user->getSettingsOrCreate();
        $settings->fill($data);
        $settings->save();

        // If language was changed, also set it in session so immediate request cycles pick it up
        if (array_key_exists('language', $data) && in_array($data['language'], ['ar','en'])) {
            session(['locale' => $data['language']]);
        }

        return response()->json(['success' => true, 'settings' => $settings]);
    }
}
