<?php

namespace App\Http\Controllers;

use App\Models\UserSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        $settings = $user->getSettingsOrCreate();
        $bumpsAdded = $user->speedBumps()->count();
        $reportsCount = $user->reports()->count();
        $activities = $user->activities()->latest()->take(10)->get();

        return view('profile.show', compact('user', 'settings', 'bumpsAdded', 'reportsCount', 'activities'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        ]);

        $user->update($validated);

        return back()->with('success', 'تم تحديث الملف الشخصي بنجاح');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if (!Hash::check($validated['current_password'], Auth::user()->password)) {
            return back()->withErrors(['current_password' => 'كلمة المرور الحالية غير صحيحة']);
        }

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'تم تغيير كلمة المرور بنجاح');
    }

    public function updateSettings(Request $request)
    {
        $user = Auth::user();
        $settings = $user->getSettingsOrCreate();

        $validated = $request->validate([
            'language' => 'required|in:ar,en',
            'theme' => 'required|in:light,dark',
            'alert_distance' => 'required|in:50,100,200',
            'notifications_enabled' => 'boolean',
            'sound_enabled' => 'boolean',
            'gps_enabled' => 'boolean',
            'motion_tracking_enabled' => 'boolean',
        ]);

        $settings->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث الإعدادات بنجاح',
            'settings' => $settings,
        ]);
    }
}
