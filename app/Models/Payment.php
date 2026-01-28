<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_id',
        'transaction_id',
        'method',
        'proof_file',
        'amount_paid',
        'approved_at',
    ];

    protected $casts = [
        'amount_paid' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    /**
     * Factura asociada
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Medio de pago asociado
     */
    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'method', 'slug');
    }

    /**
     * Aprobar pago
     */
    public function approve(): void
    {
        $this->update([
            'approved_at' => now(),
        ]);

        // Marcar factura como pagada
        if ($this->invoice) {
            $this->invoice->markAsPaid();
        }
    }

    /**
     * Verificar si es un pago de Bold
     */
    public function isBoldPayment(): bool
    {
        return $this->method === 'bold';
    }

    /**
     * Verificar si requiere aprobación manual
     */
    public function requiresApproval(): bool
    {
        $paymentMethod = $this->paymentMethod;
        return $paymentMethod ? $paymentMethod->requires_approval : true;
    }

    /**
     * Calcular comisión del medio de pago
     */
    public function getFee(): float
    {
        $paymentMethod = $this->paymentMethod;
        if ($paymentMethod) {
            return $paymentMethod->calculateFee((float) $this->amount_paid);
        }
        return 0.0;
    }

    /**
     * Verificar si está aprobado
     */
    public function isApproved(): bool
    {
        return $this->approved_at !== null;
    }
}
