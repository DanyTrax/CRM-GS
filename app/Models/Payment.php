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
        return $this->method === 'Bold';
    }

    /**
     * Verificar si está aprobado
     */
    public function isApproved(): bool
    {
        return $this->approved_at !== null;
    }
}
