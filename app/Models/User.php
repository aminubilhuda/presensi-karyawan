<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;
    // use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'photo',
        'role_id',
        'phone',
        'wa_notifications',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Mendapatkan role yang dimiliki oleh user
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Mendapatkan semua absensi yang dimiliki oleh user
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Cek apakah user adalah admin
     */
    public function isAdmin(): bool
    {
        return $this->role->name === 'Admin';
    }

    /**
     * Cek apakah user adalah guru
     */
    public function isTeacher(): bool
    {
        return $this->role->name === 'Guru';
    }

    /**
     * Cek apakah user adalah staf TU
     */
    public function isStaff(): bool
    {
        return $this->role->name === 'Staf TU';
    }

    /**
     * Check if user has specific role
     */
    public function hasRole($role)
    {
        if (is_string($role)) {
            // Jika role berisi koma, berarti multiple roles
            if (str_contains($role, ',')) {
                $roles = array_map('trim', explode(',', $role));
                return in_array($this->role->name, $roles);
            }
            return $this->role->name === $role;
        }
        return false;
    }
}