<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'subject',
        'body',
        'type',
        'recipient_type',
        'variables',
        'example_data',
        'is_active',
        'is_variation',
        'parent_template_id',
        'auto_send',
        'trigger_conditions',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_variation' => 'boolean',
        'auto_send' => 'boolean',
        'variables' => 'array',
        'example_data' => 'array',
        'trigger_conditions' => 'array',
    ];

    /**
     * Plantilla padre (si es una variación)
     */
    public function parentTemplate()
    {
        return $this->belongsTo(EmailTemplate::class, 'parent_template_id');
    }

    /**
     * Variaciones de esta plantilla
     */
    public function variations()
    {
        return $this->hasMany(EmailTemplate::class, 'parent_template_id');
    }

    /**
     * Reemplazar variables en el contenido
     */
    public function render(array $data): array
    {
        $subject = $this->subject;
        $body = $this->body;

        foreach ($data as $key => $value) {
            $subject = str_replace("{{{$key}}}", $value, $subject);
            $subject = str_replace("{{ $key }}", $value, $subject);
            $body = str_replace("{{{$key}}}", $value, $body);
            $body = str_replace("{{ $key }}", $value, $body);
        }

        return [
            'subject' => $subject,
            'body' => $body,
        ];
    }

    /**
     * Obtener plantilla por tipo y destinatario
     */
    public static function getByType(string $type, string $recipientType = 'user', bool $activeOnly = true): ?self
    {
        $query = self::where('type', $type)
            ->where('recipient_type', $recipientType)
            ->where('is_variation', false);

        if ($activeOnly) {
            $query->where('is_active', true);
        }

        return $query->first();
    }

    /**
     * Obtener todas las variaciones activas de un tipo
     */
    public static function getVariationsByType(string $type, string $recipientType = 'user'): \Illuminate\Database\Eloquent\Collection
    {
        $parent = self::getByType($type, $recipientType, false);
        
        if (!$parent) {
            return collect();
        }

        return self::where('parent_template_id', $parent->id)
            ->where('is_active', true)
            ->get();
    }
}

