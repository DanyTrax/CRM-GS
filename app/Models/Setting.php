<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
    ];

    /**
     * Obtener valor de configuración
     */
    public static function get(string $key, $default = null)
    {
        try {
            // Verificar si la tabla settings existe antes de consultar
            if (!\Illuminate\Support\Facades\Schema::hasTable('settings')) {
                return $default;
            }
            
            $setting = self::where('key', $key)->first();
            
            if (!$setting) {
                return $default;
            }

            return match($setting->type) {
                'integer' => (int) $setting->value,
                'boolean' => (bool) $setting->value,
                'json' => json_decode($setting->value, true),
                default => $setting->value,
            };
        } catch (\Exception $e) {
            // Si hay error (tabla no existe, etc.), retornar default
            return $default;
        }
    }

    /**
     * Establecer valor de configuración
     */
    public static function set(string $key, $value, string $type = 'string', string $description = null): void
    {
        try {
            // Verificar si la tabla settings existe antes de insertar/actualizar
            if (!\Illuminate\Support\Facades\Schema::hasTable('settings')) {
                \Log::warning("Tabla settings no existe. No se puede guardar el setting: {$key}");
                return;
            }
            
            self::updateOrCreate(
                ['key' => $key],
                [
                    'value' => is_array($value) ? json_encode($value) : $value,
                    'type' => $type,
                    'description' => $description,
                ]
            );
        } catch (\Exception $e) {
            \Log::error("Error al guardar setting {$key}: " . $e->getMessage());
        }
    }
}
