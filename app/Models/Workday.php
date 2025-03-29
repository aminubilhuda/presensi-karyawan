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
            return self::where('is_active', true)->pluck('day')->toArray();
        });
    }

    /**
     * Cek apakah hari ini adalah hari kerja
     */
    public static function isWorkingDay($day)
    {
        return in_array($day, self::getActiveWorkdays());
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
}