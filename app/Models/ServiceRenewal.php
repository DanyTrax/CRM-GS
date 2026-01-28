<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRenewal extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'invoice_id',
        'payment_id',
        'previous_expiration_date',
        'new_expiration_date',
        'months_added',
        'previous_cycle_months',
        'new_cycle_months',
        'differential_amount',
        'notes',
    ];

    protected $casts = [
        'previous_expiration_date' => 'date',
        'new_expiration_date' => 'date',
        'months_added' => 'integer',
        'previous_cycle_months' => 'integer',
        'new_cycle_months' => 'integer',
        'differential_amount' => 'decimal:2',
    ];

    /**
     * Servicio renovado
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Factura asociada
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Pago asociado
     */
    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}
