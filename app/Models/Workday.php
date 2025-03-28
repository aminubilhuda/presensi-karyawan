<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        return self::where('is_active', true)->pluck('day')->toArray();
    }

    /**
     * Cek apakah hari ini adalah hari kerja
     */
    public static function isWorkingDay($day)
    {
        return self::where('day', $day)
                ->where('is_active', true)
                ->exists();
    }
}
