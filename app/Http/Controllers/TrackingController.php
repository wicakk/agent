<?php

namespace App\Http\Controllers;

use App\Models\GpsLog;
use App\Models\SalesActivity;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrackingController extends Controller
{
    public function index(Request $request)
    {
        $company = Auth::user()->company;
        $user    = Auth::user();

        $salesUsers = $company->users()
            ->where('role', 'sales')
            ->where('is_active', true)
            ->get();

        // Default: tampilkan semua sales atau filter by user
        $selectedUserId = $request->user_id;
        $selectedDate   = $request->date ?? now()->format('Y-m-d');

        // Ambil aktivitas hari ini / tanggal dipilih
        $activities = SalesActivity::where('company_id', $company->id)
            ->whereDate('activity_at', $selectedDate)
            ->with(['user', 'store'])
            ->when($selectedUserId, fn($q) => $q->where('user_id', $selectedUserId))
            ->when($user->isSales(), fn($q) => $q->where('user_id', $user->id))
            ->orderBy('activity_at')
            ->get();

        // GPS logs untuk tanggal dipilih
        $gpsLogs = GpsLog::where('company_id', $company->id)
            ->whereDate('logged_at', $selectedDate)
            ->when($selectedUserId, fn($q) => $q->where('user_id', $selectedUserId))
            ->when($user->isSales(), fn($q) => $q->where('user_id', $user->id))
            ->orderBy('logged_at')
            ->with('user')
            ->get();

        // Deteksi anomali hari ini
        $anomalies = GpsLog::where('company_id', $company->id)
            ->whereDate('logged_at', $selectedDate)
            ->where(fn($q) => $q->where('is_mock_location', true)->orWhere('is_location_jump', true))
            ->with('user')
            ->get();

        // Status real-time semua sales (lokasi terakhir)
        $salesLocations = $company->users()
            ->where('role', 'sales')
            ->where('is_active', true)
            ->whereNotNull('latitude')
            ->get();

        // Statistik per sales hari ini
        $salesStats = $company->users()
            ->where('role', 'sales')
            ->where('is_active', true)
            ->withCount(['salesActivities as visits_today' => fn($q) => $q
                ->where('type', 'check_in')
                ->whereDate('activity_at', $selectedDate)
            ])
            ->withCount(['gpsLogs as gps_count' => fn($q) => $q
                ->whereDate('logged_at', $selectedDate)
            ])
            ->withCount(['gpsLogs as anomaly_count' => fn($q) => $q
                ->whereDate('logged_at', $selectedDate)
                ->where(fn($q2) => $q2->where('is_mock_location', true)->orWhere('is_location_jump', true))
            ])
            ->get();

        return view('tracking.index', compact(
            'salesUsers', 'activities', 'gpsLogs', 'anomalies',
            'salesLocations', 'salesStats', 'selectedUserId', 'selectedDate'
        ));
    }

    // API: log GPS dari mobile/JS
    public function logGps(Request $request)
    {
        $validated = $request->validate([
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy'  => 'nullable|numeric',
            'speed'     => 'nullable|numeric',
            'is_mock'   => 'nullable|boolean',
        ]);

        $user = Auth::user();

        // Deteksi location jump
        $isJump = GpsLog::detectAnomaly(
            $user,
            $validated['latitude'],
            $validated['longitude']
        );

        // Hitung jarak dari log sebelumnya
        $lastLog  = GpsLog::where('user_id', $user->id)->latest('logged_at')->first();
        $distance = null;
        if ($lastLog) {
            $distance = GpsLog::haversine(
                $lastLog->latitude, $lastLog->longitude,
                $validated['latitude'], $validated['longitude']
            );
        }

        $log = GpsLog::create([
            'company_id'            => $user->company_id,
            'user_id'               => $user->id,
            'latitude'              => $validated['latitude'],
            'longitude'             => $validated['longitude'],
            'accuracy'              => $validated['accuracy'] ?? null,
            'speed'                 => $validated['speed'] ?? null,
            'is_mock_location'      => $validated['is_mock'] ?? false,
            'is_location_jump'      => $isJump,
            'distance_from_previous'=> $distance,
            'logged_at'             => now(),
        ]);

        // Update user's last known location
        $user->update([
            'latitude'         => $validated['latitude'],
            'longitude'        => $validated['longitude'],
            'last_location_at' => now(),
        ]);

        return response()->json(['success' => true, 'anomaly' => $isJump]);
    }
}
