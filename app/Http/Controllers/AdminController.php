<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\SpeedBump;
use App\Models\Report;
use App\Models\Prediction;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!Auth::check() || !Auth::user()->is_admin) {
                abort(403, 'غير مصرح');
            }
            return $next($request);
        });
    }

    public function dashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'total_bumps' => SpeedBump::count(),
            'verified_bumps' => SpeedBump::where('is_verified', true)->count(),
            'pending_bumps' => SpeedBump::where('is_verified', false)->count(),
            'total_reports' => Report::count(),
            'predicted_bumps' => Prediction::where('is_converted', false)->count(),
        ];

        $recentBumps = SpeedBump::latest()->take(10)->get();
        $recentReports = Report::latest()->take(10)->get();
        $users = User::latest()->take(10)->get();

        return view('admin.dashboard', compact('stats', 'recentBumps', 'recentReports', 'users'));
    }

    public function users()
    {
        $users = User::paginate(15);
        return view('admin.users', compact('users'));
    }

    public function bumps()
    {
        $bumps = SpeedBump::paginate(15);
        return view('admin.bumps', compact('bumps'));
    }

    public function reports()
    {
        $reports = Report::with('user', 'speedBump')->paginate(15);
        return view('admin.reports', compact('reports'));
    }

    public function predictions()
    {
        $predictions = Prediction::where('is_converted', false)
            ->orderBy('score', 'desc')
            ->paginate(15);
        return view('admin.predictions', compact('predictions'));
    }

    public function approveBump(SpeedBump $bump)
    {
        $bump->update([
            'is_verified' => true,
            'confidence' => 100,
        ]);

        return back()->with('success', 'تم التحقق من المطب');
    }

    public function rejectBump(SpeedBump $bump)
    {
        $bump->delete();
        return back()->with('success', 'تم حذف المطب');
    }

    public function convertPrediction(Prediction $prediction)
    {
        if (!$prediction->is_converted) {
            $prediction->convertToBump();
        }

        return back()->with('success', 'تم تحويل التنبؤ إلى مطب');
    }
}
