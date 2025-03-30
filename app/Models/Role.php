<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    /**
     * Mendapatkan semua user yang memiliki role ini
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Mendapatkan semua permission yang dimiliki role ini
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permissions')
            ->withTimestamps();
    }
    
    /**
     * Memeriksa apakah role memiliki permission tertentu
     */
    public function hasPermission($permission): bool
    {
        if (is_string($permission)) {
            return $this->permissions->contains('name', $permission);
        }
        
        if (is_numeric($permission)) {
            return $this->permissions->contains('id', $permission);
        }
        
        if ($permission instanceof Permission) {
            return $this->permissions->contains('id', $permission->id);
        }
        
        return false;
    }
    
    /**
     * Memeriksa apakah role memiliki semua permission yang diberikan
     */
    public function hasAllPermissions($permissions): bool
    {
        if (is_array($permissions)) {
            foreach ($permissions as $permission) {
                if (!$this->hasPermission($permission)) {
                    return false;
                }
            }
            return true;
        }
        
        return $this->hasPermission($permissions);
    }
    
    /**
     * Memeriksa apakah role memiliki salah satu permission yang diberikan
     */
    public function hasAnyPermission($permissions): bool
    {
        if (is_array($permissions)) {
            foreach ($permissions as $permission) {
                if ($this->hasPermission($permission)) {
                    return true;
                }
            }
            return false;
        }
        
        return $this->hasPermission($permissions);
    }
    
    /**
     * Memberikan permission ke role ini
     */
    public function givePermissionTo($permission)
    {
        if (is_string($permission)) {
            $permission = Permission::where('name', $permission)->firstOrFail();
        }
        
        if (is_array($permission)) {
            $permissions = Permission::whereIn('name', $permission)->get();
            $this->permissions()->syncWithoutDetaching($permissions);
            return $this;
        }
        
        $this->permissions()->syncWithoutDetaching($permission);
        
        return $this;
    }
    
    /**
     * Mencabut permission dari role ini
     */
    public function revokePermissionTo($permission)
    {
        if (is_string($permission)) {
            $permission = Permission::where('name', $permission)->firstOrFail();
        }
        
        if (is_array($permission)) {
            $permissions = Permission::whereIn('name', $permission)->get();
            $this->permissions()->detach($permissions);
            return $this;
        }
        
        $this->permissions()->detach($permission);
        
        return $this;
    }
    
    /**
     * Sync permissions untuk role ini
     */
    public function syncPermissions($permissions)
    {
        \Log::debug('Role::syncPermissions called for role: ' . $this->name, [
            'permissions_received' => $permissions,
            'role_id' => $this->id,
            'permissions_type' => gettype($permissions)
        ]);
        
        // Jika array kosong, gunakan array kosong untuk sync, jangan konversi
        if (empty($permissions)) {
            \Log::debug('Empty permissions array, syncing with empty array');
            $syncResult = $this->permissions()->sync([]);
            
            \Log::debug('Sync result with empty array', [
                'attached' => $syncResult['attached'] ?? [],
                'detached' => $syncResult['detached'] ?? [],
            ]);
            
            return $this;
        }
        
        // Jika array string, konversi ke objek Permission
        if (is_array($permissions) && isset($permissions[0]) && is_string($permissions[0])) {
            $permissions = Permission::whereIn('name', $permissions)->get();
            \Log::debug('Permissions converted from names to objects', [
                'permissions_count' => $permissions->count()
            ]);
        }
        
        // Jika array kosong, gunakan array kosong
        if (is_array($permissions) && empty($permissions)) {
            $syncResult = $this->permissions()->sync([]);
        } else {
            // Lakukan sync dengan permissions yang ada
            $syncResult = $this->permissions()->sync($permissions);
        }
        
        \Log::debug('Sync result', [
            'attached' => $syncResult['attached'] ?? [],
            'detached' => $syncResult['detached'] ?? [],
            'updated' => $syncResult['updated'] ?? [],
        ]);
        
        return $this;
    }
}
