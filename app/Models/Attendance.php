<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
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
        
        $checkIn = \Carbon\Carbon::parse($this->date . ' ' . $this->check_in_time);
        $checkOut = \Carbon\Carbon::parse($this->date . ' ' . $this->check_out_time);
        
        // Handle if checkout is the next day
        if ($checkOut->lt($checkIn)) {
            $checkOut->addDay();
        }
        
        return $checkIn->diffInMinutes($checkOut);
    }
}
