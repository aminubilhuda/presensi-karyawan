<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AttendanceLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'latitude',
        'longitude',
        'radius',
        'is_active',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'radius' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Cek apakah lokasi tersebut berada dalam radius lokasi absensi
     */
    public function isWithinRadius($latitude, $longitude): bool
    {
        $distance = $this->calculateDistance($latitude, $longitude);
        return $distance <= $this->radius;
    }

    /**
     * Menghitung jarak antara dua titik koordinat (dalam meter)
     * Menggunakan formula Haversine untuk menghitung jarak dua titik di permukaan bumi
     */
    protected function calculateDistance($latitude, $longitude): float
    {
        $earthRadius = 6371000; // Radius bumi dalam meter

        $latFrom = deg2rad($this->latitude);
        $lonFrom = deg2rad($this->longitude);
        $latTo = deg2rad($latitude);
        $lonTo = deg2rad($longitude);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        
        return $angle * $earthRadius;
    }

    /**
     * Mendapatkan lokasi absensi yang aktif dengan cache
     */
    public static function getActiveLocations()
    {
        return Cache::remember('active_attendance_locations', 60 * 24, function() {
            return self::where('is_active', true)->get();
        });
    }
    
    /**
     * Menghapus cache saat ada perubahan di model
     */
    public static function bootAttendanceLocation()
    {
        static::saved(function () {
            Cache::forget('active_attendance_locations');
        });
        
        static::deleted(function () {
            Cache::forget('active_attendance_locations');
        });
    }
    
    /**
     * Memformat hasil jarak ke dalam bentuk string jarak (m, km)
     *
     * @param float $distance
     * @return string
     */
    public static function formatDistance(float $distance): string
    {
        if ($distance < 1000) {
            return round($distance) . ' m';
        } else {
            return round($distance / 1000, 2) . ' km';
        }
    }
}
