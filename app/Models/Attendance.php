<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'check_in_time',
        'check_out_time',
        'check_in_photo',
        'check_out_photo',
        'check_in_latitude',
        'check_in_longitude',
        'check_out_latitude',
        'check_out_longitude',
        'status',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'check_in_time' => 'string',
        'check_out_time' => 'string',
        'check_in_latitude' => 'decimal:7',
        'check_in_longitude' => 'decimal:7',
        'check_out_latitude' => 'decimal:7',
        'check_out_longitude' => 'decimal:7',
    ];

    /**
     * Get the user that owns the attendance record
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get working duration in minutes
     */
    public function getWorkingDurationAttribute()
    {
        if (!$this->check_in_time || !$this->check_out_time) {
            return 0;
        }
        
        // Buat objek Carbon dengan timezone Jakarta
        $dateStr = $this->date->format('Y-m-d');
        $checkIn = Carbon::createFromFormat('Y-m-d H:i:s', "$dateStr $this->check_in_time", 'Asia/Jakarta');
        $checkOut = Carbon::createFromFormat('Y-m-d H:i:s', "$dateStr $this->check_out_time", 'Asia/Jakarta');
        
        // Handle jika checkout di hari berikutnya (waktu checkout lebih kecil dari waktu checkin)
        if ($checkOut->lt($checkIn)) {
            $checkOut->addDay();
        }
        
        return $checkIn->diffInMinutes($checkOut);
    }

    /**
     * Get formatted working duration in hours and minutes
     */
    public function getFormattedDurationAttribute()
    {
        $minutes = $this->getWorkingDurationAttribute();
        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;
        
        return "{$hours} jam {$remainingMinutes} menit";
    }
    
    /**
     * Scope untuk mendapatkan absensi pada hari tertentu
     */
    public function scopeOnDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }
    
    /**
     * Scope untuk mendapatkan absensi berdasarkan status
     */
    public function scopeWithStatus($query, $status)
    {
        if (is_array($status)) {
            return $query->whereIn('status', $status);
        }
        return $query->where('status', $status);
    }
}