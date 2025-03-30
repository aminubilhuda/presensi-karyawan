<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class LeaveRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'start_date',
        'end_date',
        'type',
        'attachment',
        'reason',
        'status',
        'admin_notes',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime',
    ];

    /**
     * Get the user that owns the leave request
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin that approved/rejected the leave request
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
    
    /**
     * Get the duration of leave request in days
     */
    public function getDurationAttribute()
    {
        if (!$this->start_date || !$this->end_date) {
            return 0;
        }
        
        return $this->start_date->diffInDays($this->end_date) + 1;
    }
    
    /**
     * Check if the leave request is pending
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }
    
    /**
     * Check if the leave request is approved
     */
    public function isApproved()
    {
        return $this->status === 'approved';
    }
    
    /**
     * Check if the leave request is rejected
     */
    public function isRejected()
    {
        return $this->status === 'rejected';
    }
    
    /**
     * Scope query to only include pending leave requests
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
    
    /**
     * Scope query to only include approved leave requests
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
    
    /**
     * Scope query to only include rejected leave requests
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }
    
    /**
     * Scope query to only include active leave requests
     * (start_date <= today <= end_date)
     */
    public function scopeActive($query)
    {
        $today = Carbon::today();
        return $query->where('status', 'approved')
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today);
    }
} 