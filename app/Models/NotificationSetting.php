<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'module',
        'event_type',
        'recipient_type',
        'email_enabled',
        'sms_enabled',
        'push_enabled',
        'in_app_enabled',
        'template_id',
        'conditions',
        'description',
    ];

    protected $casts = [
        'email_enabled' => 'boolean',
        'sms_enabled' => 'boolean',
        'push_enabled' => 'boolean',
        'in_app_enabled' => 'boolean',
        'conditions' => 'array',
    ];

    /**
     * Plantilla de email asociada
     */
    public function template()
    {
        return $this->belongsTo(EmailTemplate::class, 'template_id');
    }

    /**
     * Verificar si una notificación está habilitada
     */
    public static function isEnabled(string $module, string $eventType, string $recipientType, string $channel = 'email'): bool
    {
        $setting = self::where('module', $module)
            ->where('event_type', $eventType)
            ->where('recipient_type', $recipientType)
            ->first();

        if (!$setting) {
            return false; // Por defecto deshabilitado si no existe configuración
        }

        return match($channel) {
            'email' => $setting->email_enabled,
            'sms' => $setting->sms_enabled,
            'push' => $setting->push_enabled,
            'in_app' => $setting->in_app_enabled,
            default => false,
        };
    }

    /**
     * Obtener plantilla para una notificación
     */
    public static function getTemplate(string $module, string $eventType, string $recipientType): ?EmailTemplate
    {
        $setting = self::where('module', $module)
            ->where('event_type', $eventType)
            ->where('recipient_type', $recipientType)
            ->first();

        if (!$setting || !$setting->template_id) {
            return null;
        }

        return $setting->template;
    }
}
