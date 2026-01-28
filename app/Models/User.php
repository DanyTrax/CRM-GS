<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'avatar',
    ];
    
    /**
     * Prevenir que se intente guardar campos que no existen
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($user) {
            // Eliminar campos que no existen en la tabla
            if (isset($user->attributes['two_factor_enabled'])) {
                unset($user->attributes['two_factor_enabled']);
            }
            if (isset($user->attributes['google2fa_enabled'])) {
                unset($user->attributes['google2fa_enabled']);
            }
            if (isset($user->attributes['google2fa_secret'])) {
                unset($user->attributes['google2fa_secret']);
            }
            if (isset($user->attributes['status'])) {
                unset($user->attributes['status']);
            }
        });
        
        static::updating(function ($user) {
            // Eliminar campos que no existen en la tabla
            if (isset($user->attributes['two_factor_enabled'])) {
                unset($user->attributes['two_factor_enabled']);
            }
            if (isset($user->attributes['google2fa_enabled'])) {
                unset($user->attributes['google2fa_enabled']);
            }
            if (isset($user->attributes['google2fa_secret'])) {
                unset($user->attributes['google2fa_secret']);
            }
            if (isset($user->attributes['status'])) {
                unset($user->attributes['status']);
            }
        });
    }

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'two_factor_recovery_codes' => 'array',
    ];

    /**
     * Rol del usuario
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Verificar si el usuario tiene un rol específico
     */
    public function hasRole(string $roleSlug): bool
    {
        return $this->role && $this->role->slug === $roleSlug;
    }

    /**
     * Verificar si el usuario es Super Admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super-admin');
    }
}
