<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class Holiday extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'date',
        'description',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /**
     * Scope untuk mendapatkan hari libur di bulan tertentu
     */
    public function scopeMonth($query, $month, $year)
    {
        return $query->whereMonth('date', $month)
                    ->whereYear('date', $year);
    }

    /**
     * Scope untuk mendapatkan hari libur pada tahun tertentu
     */
    public function scopeYear($query, $year)
    {
        return $query->whereYear('date', $year);
    }
    
    /**
     * Cek apakah tanggal tertentu adalah hari libur
     * 
     * @param string|Carbon $date
     * @return bool
     */
    public static function isHoliday($date)
    {
        if ($date instanceof Carbon) {
            $dateString = $date->toDateString();
        } else {
            $dateString = $date;
        }
        
        // Caching untuk performa
        return Cache::remember('holiday_' . $dateString, 60 * 24, function() use ($dateString) {
            return self::whereDate('date', $dateString)->exists();
        });
    }
    
    /**
     * Mendapatkan semua hari libur dalam rentang waktu tertentu
     * 
     * @param string|Carbon $startDate
     * @param string|Carbon $endDate
     * @return array
     */
    public static function getHolidaysInRange($startDate, $endDate)
    {
        if ($startDate instanceof Carbon) {
            $startDate = $startDate->toDateString();
        }
        
        if ($endDate instanceof Carbon) {
            $endDate = $endDate->toDateString();
        }
        
        return self::whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->get();
    }
}
