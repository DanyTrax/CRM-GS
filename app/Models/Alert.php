<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Alert extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'entity_type',
        'entity_id',
        'message',
        'priority',
        'status',
        'trigger_date',
        'sent_at',
        'resolved_at',
        'metadata',
    ];

    protected $casts = [
        'trigger_date' => 'date',
        'sent_at' => 'date',
        'resolved_at' => 'date',
        'metadata' => 'array',
    ];

    /**
     * Obtener la entidad relacionada
     */
    public function entity()
    {
        return $this->morphTo('entity', 'entity_type', 'entity_id');
    }

    /**
     * Marcar como enviada
     */
    public function markAsSent(): void
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    /**
     * Marcar como resuelta
     */
    public function markAsResolved(): void
    {
        $this->update([
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);
    }

    /**
     * Descartar alerta
     */
    public function dismiss(): void
    {
        $this->update(['status' => 'dismissed']);
    }

    /**
     * Verificar si está activa (pendiente y fecha de activación llegó)
     */
    public function isActive(): bool
    {
        return $this->status === 'pending' && 
               $this->trigger_date <= now();
    }

    /**
     * Crear alerta para servicio próximo a vencer
     */
    public static function createServiceExpiringAlert($service, int $daysBefore = 7): self
    {
        return self::create([
            'name' => "Servicio próximo a vencer: {$service->name}",
            'type' => 'service_expiring',
            'entity_type' => Service::class,
            'entity_id' => $service->id,
            'message' => "El servicio '{$service->name}' del cliente '{$service->client->company_name}' vence el {$service->next_due_date->format('d/m/Y')}",
            'priority' => $daysBefore <= 3 ? 'high' : 'medium',
            'status' => 'pending',
            'trigger_date' => $service->next_due_date->subDays($daysBefore),
            'metadata' => [
                'service_id' => $service->id,
                'client_id' => $service->client_id,
                'due_date' => $service->next_due_date->toDateString(),
                'days_before' => $daysBefore,
            ],
        ]);
    }

    /**
     * Crear alerta para factura vencida
     */
    public static function createInvoiceOverdueAlert($invoice): self
    {
        return self::create([
            'name' => "Factura vencida: {$invoice->invoice_number}",
            'type' => 'invoice_overdue',
            'entity_type' => Invoice::class,
            'entity_id' => $invoice->id,
            'message' => "La factura {$invoice->invoice_number} del cliente '{$invoice->client->company_name}' está vencida desde el {$invoice->due_date->format('d/m/Y')}",
            'priority' => 'high',
            'status' => 'pending',
            'trigger_date' => $invoice->due_date,
            'metadata' => [
                'invoice_id' => $invoice->id,
                'client_id' => $invoice->client_id,
                'due_date' => $invoice->due_date->toDateString(),
                'amount' => $invoice->total_amount,
            ],
        ]);
    }

    /**
     * Crear alerta para pago pendiente
     */
    public static function createPaymentPendingAlert($payment): self
    {
        return self::create([
            'name' => "Pago pendiente de aprobación",
            'type' => 'payment_pending',
            'entity_type' => Payment::class,
            'entity_id' => $payment->id,
            'message' => "El pago de {$payment->amount_paid} COP para la factura {$payment->invoice->invoice_number} está pendiente de aprobación",
            'priority' => 'medium',
            'status' => 'pending',
            'trigger_date' => now(),
            'metadata' => [
                'payment_id' => $payment->id,
                'invoice_id' => $payment->invoice_id,
                'amount' => $payment->amount_paid,
            ],
        ]);
    }
}
