<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'type',
        'duration_value',
        'duration_unit',
        'price',
        'currency',
        'tax_enabled',
        'tax_percentage',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'tax_enabled' => 'boolean',
        'tax_percentage' => 'decimal:2',
        'duration_value' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    /**
     * Servicios creados desde este producto
     */
    public function services()
    {
        return $this->hasMany(Service::class, 'product_id');
    }

    /**
     * Calcular fecha de vencimiento basada en la duración del producto
     */
    public function calculateExpirationDate(Carbon $startDate = null): Carbon
    {
        $startDate = $startDate ?? now();

        if ($this->type === 'one_time' || !$this->duration_value || !$this->duration_unit) {
            return $startDate;
        }

        return match($this->duration_unit) {
            'days' => $startDate->copy()->addDays($this->duration_value),
            'months' => $startDate->copy()->addMonths($this->duration_value),
            'years' => $startDate->copy()->addYears($this->duration_value),
            default => $startDate,
        };
    }

    /**
     * Obtener duración formateada
     */
    public function getFormattedDuration(): string
    {
        if ($this->type === 'one_time') {
            return 'Un solo consumo';
        }

        if (!$this->duration_value || !$this->duration_unit) {
            return 'Sin duración definida';
        }

        $unit = match($this->duration_unit) {
            'days' => $this->duration_value === 1 ? 'día' : 'días',
            'months' => $this->duration_value === 1 ? 'mes' : 'meses',
            'years' => $this->duration_value === 1 ? 'año' : 'años',
            default => $this->duration_unit,
        };

        return "{$this->duration_value} {$unit}";
    }

    /**
     * Calcular precio con impuesto
     */
    public function getPriceWithTax(): float
    {
        if (!$this->tax_enabled || $this->tax_percentage == 0) {
            return $this->price;
        }

        return round($this->price * (1 + ($this->tax_percentage / 100)), 2);
    }

    /**
     * Calcular monto del impuesto
     */
    public function getTaxAmount(): float
    {
        if (!$this->tax_enabled || $this->tax_percentage == 0) {
            return 0;
        }

        return round($this->price * ($this->tax_percentage / 100), 2);
    }
}
