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
     * Obtiene un setting por su key
     */
    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        
        if (!$setting) {
            return $default;
        }

        return match($setting->type) {
            'integer' => (int) $setting->value,
            'boolean' => (bool) $setting->value,
            'json' => json_decode($setting->value, true),
            default => $setting->value,
        };
    }

    /**
     * Establece un setting
     */
    public static function set(string $key, $value, string $type = 'string'): void
    {
        $value = match($type) {
            'json' => json_encode($value),
            'boolean' => $value ? '1' : '0',
            default => (string) $value,
        };

        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'type' => $type]
        );
    }
}
