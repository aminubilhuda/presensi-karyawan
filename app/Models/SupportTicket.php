<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ticket_id',
        'subject',
        'message',
        'priority',
        'status',
        'assigned_to',
        'closed_at'
    ];
    
    protected $casts = [
        'closed_at' => 'datetime',
    ];
    
    /**
     * Mendapatkan pengguna yang membuat tiket
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Mendapatkan admin yang ditugaskan ke tiket
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
    
    /**
     * Menandai tiket sebagai ditutup
     */
    public function close(): void
    {
        $this->status = 'closed';
        $this->closed_at = now();
        $this->save();
    }
}
