<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id',
        'product_id',
        'name',
        'description',
        'type',
        'currency',
        'price',
        'tax_enabled',
        'tax_percentage',
        'billing_cycle',
        'next_due_date',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'tax_enabled' => 'boolean',
        'tax_percentage' => 'decimal:2',
        'next_due_date' => 'date',
        'billing_cycle' => 'integer',
    ];

    /**
     * Cliente propietario del servicio
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Producto del cual se creó el servicio (opcional)
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Facturas asociadas al servicio
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Renovar servicio - LÓGICA ANTI-FRAUDE
     * 
     * @param int $monthsToAdd Meses a agregar (por defecto usa billing_cycle)
     * @param Carbon|null $paymentDate Fecha del pago (opcional, por defecto now())
     * @return Service
     */
    public function renew(int $monthsToAdd = null, Carbon $paymentDate = null): self
    {
        if ($this->type !== 'recurrente') {
            throw new \Exception('Solo los servicios recurrentes pueden renovarse');
        }

        $monthsToAdd = $monthsToAdd ?? $this->billing_cycle;
        $paymentDate = $paymentDate ?? now();

        // LÓGICA ANTI-FRAUDE: La nueva fecha se calcula desde la fecha de vencimiento anterior
        $previousDueDate = $this->next_due_date;
        
        // Calcular nueva fecha de vencimiento (desde la fecha anterior + periodo)
        $newDueDate = Carbon::parse($previousDueDate)->addMonths($monthsToAdd);

        // Actualizar la fecha del servicio
        $this->update([
            'next_due_date' => $newDueDate->toDateString(),
        ]);

        return $this;
    }

    /**
     * Cambiar ciclo de facturación (Upselling)
     */
    public function changeBillingCycle(int $newCycleMonths): array
    {
        if ($this->type !== 'recurrente') {
            throw new \Exception('Solo los servicios recurrentes pueden cambiar ciclo');
        }

        $oldCycle = $this->billing_cycle;
        $oldPricePerMonth = $this->price / $oldCycle;

        // Calcular días transcurridos desde el último pago
        $daysElapsed = now()->diffInDays($this->next_due_date);
        $totalDaysInCycle = $oldCycle * 30;
        $daysRemaining = max(0, $totalDaysInCycle - $daysElapsed);

        // Calcular nuevo precio proporcional
        $newPrice = $newCycleMonths * $oldPricePerMonth;
        $differentialAmount = $newPrice - ($this->price - (($daysElapsed / $totalDaysInCycle) * $this->price));

        // Actualizar el ciclo
        $this->update(['billing_cycle' => $newCycleMonths]);

        return [
            'differential_amount' => round($differentialAmount, 2),
            'days_remaining' => $daysRemaining,
            'old_cycle_months' => $oldCycle,
            'new_cycle_months' => $newCycleMonths,
            'old_price' => $this->price,
            'new_price' => $newPrice,
        ];
    }

    /**
     * Verificar si el servicio está vencido
     */
    public function isExpired(): bool
    {
        return $this->next_due_date < now();
    }

    /**
     * Verificar si el servicio está próximo a vencer (7 días)
     */
    public function isExpiringSoon(): bool
    {
        return $this->next_due_date->diffInDays(now()) <= 7 && !$this->isExpired();
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

    /**
     * Convertir precio a COP si es USD
     * Aplica TRM + Spread + Tolerancia de redondeo
     */
    public function getPriceInCOP(): float
    {
        if ($this->currency === 'COP') {
            return $this->getPriceWithTax();
        }

        // Obtener TRM (automática o manual según configuración)
        $trmBase = (float) \App\Services\ExchangeRateService::getTRM();
        $spread = (float) \App\Models\Setting::get('bold_spread_percentage', 3);
        $toleranceType = \App\Models\Setting::get('exchange_tolerance_type', 'percentage'); // 'percentage' o 'fixed'
        $toleranceValue = (float) \App\Models\Setting::get('exchange_tolerance_value', 0);

        // Calcular TRM con spread
        $trmWithSpread = $trmBase * (1 + ($spread / 100));

        // Aplicar tolerancia
        if ($toleranceType === 'percentage') {
            $finalRate = $trmWithSpread * (1 + ($toleranceValue / 100));
        } else {
            // Tolerancia fija (sumar valor)
            $finalRate = $trmWithSpread + $toleranceValue;
        }

        // Convertir precio con impuesto
        $priceWithTax = $this->getPriceWithTax();
        $priceInCOP = $priceWithTax * $finalRate;

        // Redondear según configuración
        $rounding = \App\Models\Setting::get('exchange_rounding', 'up'); // 'up', 'down', 'nearest'
        
        return match($rounding) {
            'up' => ceil($priceInCOP),
            'down' => floor($priceInCOP),
            'nearest' => round($priceInCOP),
            default => round($priceInCOP),
        };
    }
}
