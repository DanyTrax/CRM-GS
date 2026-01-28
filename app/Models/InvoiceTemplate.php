<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'html_content',
        'variables',
        'is_active',
        'is_default',
        'description',
    ];

    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    /**
     * Reemplazar variables en el contenido HTML
     */
    public function render(array $data): string
    {
        $content = $this->html_content;
        
        foreach ($data as $key => $value) {
            $content = str_replace("{{{$key}}}", $value, $content);
            $content = str_replace("{{ $key }}", $value, $content);
        }
        
        return $content;
    }

    /**
     * Obtener plantilla por defecto según tipo
     */
    public static function getDefault(string $type = 'invoice'): ?self
    {
        return self::where('type', $type)
            ->where('is_default', true)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Marcar como plantilla por defecto
     */
    public function setAsDefault(): void
    {
        // Desmarcar otras plantillas del mismo tipo
        self::where('type', $this->type)
            ->where('id', '!=', $this->id)
            ->update(['is_default' => false]);
        
        // Marcar esta como defecto
        $this->update(['is_default' => true]);
    }
}
