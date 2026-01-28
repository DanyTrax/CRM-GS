<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentMethod extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'type',
        'provider',
        'is_active',
        'is_default',
        'configuration',
        'settings',
        'description',
        'instructions',
        'icon',
        'fee_percentage',
        'fee_fixed',
        'min_amount',
        'max_amount',
        'accepted_currencies',
        'requires_approval',
        'auto_approve',
        'webhook_url',
        'webhook_secret',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'configuration' => 'array',
        'settings' => 'array',
        'accepted_currencies' => 'array',
        'fee_percentage' => 'decimal:2',
        'fee_fixed' => 'decimal:2',
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'requires_approval' => 'boolean',
        'auto_approve' => 'boolean',
    ];

    /**
     * Pagos usando este medio de pago
     */
    public function payments()
    {
        return $this->hasMany(Payment::class, 'method', 'slug');
    }

    /**
     * Obtener medio de pago por defecto
     */
    public static function getDefault(): ?self
    {
        return self::where('is_default', true)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Obtener medios de pago activos
     */
    public static function getActive(): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('is_active', true)
            ->orderBy('is_default', 'desc')
            ->orderBy('name', 'asc')
            ->get();
    }

    /**
     * Marcar como medio de pago por defecto
     */
    public function setAsDefault(): void
    {
        // Desmarcar otros medios de pago
        self::where('id', '!=', $this->id)
            ->update(['is_default' => false]);
        
        // Marcar este como defecto
        $this->update(['is_default' => true]);
    }

    /**
     * Calcular comisión para un monto
     */
    public function calculateFee(float $amount): float
    {
        $fee = 0;
        
        // Comisión porcentual
        if ($this->fee_percentage > 0) {
            $fee += ($amount * ($this->fee_percentage / 100));
        }
        
        // Comisión fija
        $fee += $this->fee_fixed;
        
        return round($fee, 2);
    }

    /**
     * Verificar si un monto está dentro de los límites
     */
    public function isAmountValid(float $amount): bool
    {
        if ($this->min_amount && $amount < $this->min_amount) {
            return false;
        }
        
        if ($this->max_amount && $amount > $this->max_amount) {
            return false;
        }
        
        return true;
    }

    /**
     * Verificar si acepta una moneda
     */
    public function acceptsCurrency(string $currency): bool
    {
        if (!$this->accepted_currencies || empty($this->accepted_currencies)) {
            return true; // Si no hay restricción, acepta todas
        }
        
        return in_array(strtoupper($currency), array_map('strtoupper', $this->accepted_currencies));
    }
}
