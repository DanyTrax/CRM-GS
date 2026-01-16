<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use PragmaRX\Google2FA\Google2FA;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'google2fa_secret',
        'google2fa_enabled',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'google2fa_secret',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'google2fa_enabled' => 'boolean',
        ];
    }

    public function client()
    {
        return $this->hasOne(Client::class);
    }

    public function impersonationLogs()
    {
        return $this->hasMany(ImpersonationLog::class, 'impersonator_id');
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function verifyGoogle2FA($code): bool
    {
        if (!$this->google2fa_enabled || !$this->google2fa_secret) {
            return false;
        }

        $google2fa = new Google2FA();
        return $google2fa->verifyKey($this->google2fa_secret, $code);
    }
}
