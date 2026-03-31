<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GpsLog extends Model
{
    protected $fillable = [
        'company_id', 'user_id', 'latitude', 'longitude', 'accuracy',
        'speed', 'altitude', 'is_mock_location', 'is_location_jump',
        'distance_from_previous', 'logged_at',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'accuracy' => 'decimal:2',
        'speed' => 'decimal:2',
        'altitude' => 'decimal:2',
        'is_mock_location' => 'boolean',
        'is_location_jump' => 'boolean',
        'distance_from_previous' => 'decimal:2',
        'logged_at' => 'datetime',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function company(): BelongsTo { return $this->belongsTo(Company::class); }

    /**
     * Detect location jump anomaly (> 500m in < 30 seconds)
     */
    public static function detectAnomaly(User $user, float $lat, float $lng): bool
    {
        $last = static::where('user_id', $user->id)
            ->orderBy('logged_at', 'desc')
            ->first();

        if (!$last) return false;

        $timeDiff = now()->diffInSeconds($last->logged_at);
        $distance = static::haversine($last->latitude, $last->longitude, $lat, $lng);

        // Flag if moved > 500m in < 30 seconds (impossible without teleportation)
        return ($timeDiff < 30 && $distance > 500);
    }

    /**
     * Haversine formula to calculate distance in meters
     */
    public static function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $R = 6371000; // meters
        $phi1 = deg2rad($lat1);
        $phi2 = deg2rad($lat2);
        $dphi = deg2rad($lat2 - $lat1);
        $dlambda = deg2rad($lng2 - $lng1);

        $a = sin($dphi / 2) ** 2 + cos($phi1) * cos($phi2) * sin($dlambda / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $R * $c;
    }
}
