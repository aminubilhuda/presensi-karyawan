<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
