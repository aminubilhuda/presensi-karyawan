<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    use HasFactory;
    
    protected $fillable = ['name', 'display_name', 'description', 'module'];
    
    /**
     * Mendapatkan semua role yang memiliki permission ini
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permissions')
            ->withTimestamps();
    }
    
    /**
     * Mengelompokkan permissions berdasarkan module
     */
    public static function getGroupedByModule()
    {
        return self::orderBy('module')->orderBy('display_name')->get()->groupBy('module');
    }
} 