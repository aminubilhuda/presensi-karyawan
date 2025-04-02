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
     * Memeriksa apakah koordinat berada dalam radius lokasi ini
     */
    public function isWithinRadius($latitude, $longitude)
    {
        // Toleransi langsung untuk koordinat yang sangat dekat (untuk mengatasi ketidakakuratan GPS)
        $latDiff = abs($this->latitude - $latitude);
        $lonDiff = abs($this->longitude - $longitude);
        
        // Jika koordinat sangat dekat (selisih kurang dari 0.0005 derajat, sekitar 55 meter)
        // Langsung anggap berada dalam area
        if ($latDiff < 0.0005 && $lonDiff < 0.0005) {
            \Log::info('Lokasi sangat dekat dengan titik absensi, diizinkan otomatis', [
                'location_name' => $this->name,
                'lat_diff' => $latDiff,
                'lon_diff' => $lonDiff,
                'submitted_coords' => [$latitude, $longitude],
                'location_coords' => [$this->latitude, $this->longitude]
            ]);
            return true;
        }
        
        // Tambahkan buffer untuk toleransi ketidakakuratan GPS
        $bufferRadius = 200; // Ditingkatkan menjadi 200 meter untuk toleransi lebih besar
        $totalRadius = $this->radius + $bufferRadius;
        
        // Konversi totalRadius ke kilometer
        $totalRadiusKm = $totalRadius / 1000;
        
        $distance = $this->calculateDistance($latitude, $longitude);
        
        // Log untuk debugging - tambahkan user info jika bisa diperoleh
        $userName = '';
        $userId = '';
        if (auth()->check()) {
            $userName = auth()->user()->name;
            $userId = auth()->user()->id;
        }
        
        \Log::info('Location check', [
            'user_id' => $userId,
            'user_name' => $userName,
            'submitted_coordinates' => [$latitude, $longitude],
            'location_id' => $this->id,
            'location_name' => $this->name,
            'location_coordinates' => [$this->latitude, $this->longitude],
            'distance' => $distance,
            'distance_formatted' => self::formatDistance($distance),
            'radius' => $this->radius / 1000,
            'buffer' => $bufferRadius / 1000,
            'total_radius' => $totalRadiusKm,
            'is_within' => $distance <= $totalRadiusKm
        ]);
        
        return $distance <= $totalRadiusKm;
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
