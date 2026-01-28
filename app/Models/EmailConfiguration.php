<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailConfiguration extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'provider',
        'is_active',
        'is_default',
        'smtp_host',
        'smtp_port',
        'smtp_encryption',
        'smtp_username',
        'smtp_password',
        'zoho_client_id',
        'zoho_client_secret',
        'zoho_refresh_token',
        'zoho_access_token',
        'zoho_token_expires_at',
        'from_email',
        'from_name',
        'reply_to_email',
        'reply_to_name',
        'rate_limit',
        'settings',
        'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'smtp_port' => 'integer',
        'rate_limit' => 'integer',
        'settings' => 'array',
        'zoho_token_expires_at' => 'datetime',
    ];

    /**
     * Obtener configuración por defecto
     */
    public static function getDefault(): ?self
    {
        return self::where('is_default', true)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Marcar como configuración por defecto
     */
    public function setAsDefault(): void
    {
        // Desmarcar otras configuraciones
        self::where('id', '!=', $this->id)
            ->update(['is_default' => false]);
        
        // Marcar esta como defecto
        $this->update(['is_default' => true]);
    }

    /**
     * Aplicar configuración a Laravel Mail
     */
    public function applyToMailConfig(): void
    {
        config([
            'mail.mailers.smtp.host' => $this->smtp_host,
            'mail.mailers.smtp.port' => $this->smtp_port,
            'mail.mailers.smtp.encryption' => $this->smtp_encryption,
            'mail.mailers.smtp.username' => $this->smtp_username,
            'mail.mailers.smtp.password' => $this->smtp_password,
            'mail.from.address' => $this->from_email,
            'mail.from.name' => $this->from_name,
        ]);
    }
}
