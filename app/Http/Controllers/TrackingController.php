<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ScopesByBranch;
use App\Models\GpsLog;
use App\Models\SalesActivity;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrackingController extends Controller
{
    use ScopesByBranch;

    public function index(Request $request)
    {
        $user = Auth::user();

        $salesUsers  = $this->branchScope(User::query())
            ->where('role', 'sales')->where('is_active', true)->get();

        $selectedUserId = $request->user_id;
        $selectedDate   = $request->date ?? now()->format('Y-m-d');

        $activities = $this->branchScope(SalesActivity::query())
            ->whereDate('activity_at', $selectedDate)
            ->with(['user', 'store'])
            ->when($selectedUserId, fn($q) => $q->where('user_id', $selectedUserId))
            ->when($user->isSales(), fn($q) => $q->where('user_id', $user->id))
            ->orderBy('activity_at')
            ->get();

        $gpsLogs = $this->branchScope(GpsLog::query())
            ->whereDate('logged_at', $selectedDate)
            ->when($selectedUserId, fn($q) => $q->where('user_id', $selectedUserId))
            ->when($user->isSales(), fn($q) => $q->where('user_id', $user->id))
            ->orderBy('logged_at')
            ->with('user')
            ->get();

        $anomalies = $this->branchScope(GpsLog::query())
            ->whereDate('logged_at', $selectedDate)
            ->where(fn($q) => $q->where('is_mock_location', true)->orWhere('is_location_jump', true))
            ->with('user')
            ->get();

        $salesLocations = $this->branchScope(User::query())
            ->where('role', 'sales')->where('is_active', true)->whereNotNull('latitude')->get();

        $salesStats = $this->branchScope(User::query())
            ->where('role', 'sales')->where('is_active', true)
            ->withCount(['salesActivities as visits_today' => fn($q) => $q->where('type','check_in')->whereDate('activity_at', $selectedDate)])
            ->withCount(['gpsLogs as gps_count'           => fn($q) => $q->whereDate('logged_at', $selectedDate)])
            ->withCount(['gpsLogs as anomaly_count'       => fn($q) => $q->whereDate('logged_at', $selectedDate)->where(fn($q2) => $q2->where('is_mock_location', true)->orWhere('is_location_jump', true))])
            ->get();

        return view('tracking.index', compact(
            'salesUsers', 'activities', 'gpsLogs', 'anomalies',
            'salesLocations', 'salesStats', 'selectedUserId', 'selectedDate'
        ));
    }

    public function logGps(Request $request)
    {
        $validated = $request->validate([
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy'  => 'nullable|numeric',
            'speed'     => 'nullable|numeric',
            'is_mock'   => 'nullable|boolean',
        ]);

        $user     = Auth::user();
        $isJump   = GpsLog::detectAnomaly($user, $validated['latitude'], $validated['longitude']);
        $lastLog  = GpsLog::where('user_id', $user->id)->latest('logged_at')->first();
        $distance = $lastLog
            ? GpsLog::haversine($lastLog->latitude, $lastLog->longitude, $validated['latitude'], $validated['longitude'])
            : null;

        GpsLog::create([
            'company_id'             => $user->company_id,
            'branch_id'              => $user->branch_id,
            'user_id'                => $user->id,
            'latitude'               => $validated['latitude'],
            'longitude'              => $validated['longitude'],
            'accuracy'               => $validated['accuracy'] ?? null,
            'speed'                  => $validated['speed'] ?? null,
            'is_mock_location'       => $validated['is_mock'] ?? false,
            'is_location_jump'       => $isJump,
            'distance_from_previous' => $distance,
            'logged_at'              => now(),
        ]);

        $user->update([
            'latitude'         => $validated['latitude'],
            'longitude'        => $validated['longitude'],
            'last_location_at' => now(),
        ]);

        return response()->json(['success' => true, 'anomaly' => $isJump]);
    }
}
