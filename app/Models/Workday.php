<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Workday extends Model
{
    use HasFactory;

    protected $fillable = [
        'day',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Mendapatkan semua hari kerja yang aktif
     */
    public static function getActiveWorkdays()
    {
        return Cache::remember('active_workdays', 60 * 24, function() {
            $activeWorkdays = self::where('is_active', true)->pluck('day')->toArray();
            \Log::info('Active workdays', ['days' => $activeWorkdays]);
            return $activeWorkdays;
        });
    }

    /**
     * Cek apakah hari ini adalah hari kerja
     */
    public static function isWorkingDay($day)
    {
        $activeWorkdays = self::getActiveWorkdays();
        $result = in_array($day, $activeWorkdays);
        \Log::info('Checking if day is a working day', [
            'day' => $day,
            'active_workdays' => $activeWorkdays,
            'is_working_day' => $result
        ]);
        return $result;
    }
    
    /**
     * Reset cache ketika ada perubahan data
     */
    public static function bootWorkday()
    {
        static::saved(function () {
            Cache::forget('active_workdays');
        });
        
        static::deleted(function () {
            Cache::forget('active_workdays');
        });
    }

    /**
     * Clear the active workdays cache
     */
    public static function clearActiveWorkdaysCache()
    {
        Cache::forget('active_workdays');
        return true;
    }
}