<?php

namespace App\Services;

use App\Models\Service;
use App\Models\Payment;
use Carbon\Carbon;

class ServiceRenewalService
{
    /**
     * Renueva un servicio basándose en el pago recibido
     * IMPORTANTE: Usa current_due_date, NO payment_date (anti-fraude)
     */
    public function renewServiceFromPayment(Payment $payment): void
    {
        if (!$payment->service_id) {
            return;
        }

        $service = Service::find($payment->service_id);
        
        if (!$service || $service->type !== 'recurring') {
            return;
        }

        // Usar current_due_date como base, NO payment_date
        $baseDate = $service->current_due_date ?? Carbon::now();
        
        // Calcular nueva fecha según el ciclo
        $newDueDate = $this->calculateNextDueDate($baseDate, $service->billing_cycle);

        $service->update([
            'current_due_date' => $newDueDate,
            'next_due_date' => $newDueDate,
            'status' => 'active',
        ]);
    }

    /**
     * Calcula la próxima fecha de vencimiento
     */
    protected function calculateNextDueDate(Carbon $fromDate, string $cycle): Carbon
    {
        return match($cycle) {
            'monthly' => $fromDate->copy()->addMonth(),
            'quarterly' => $fromDate->copy()->addMonths(3),
            'semiannual' => $fromDate->copy()->addMonths(6),
            'annual' => $fromDate->copy()->addYear(),
            'biannual' => $fromDate->copy()->addYears(2),
            'triennal' => $fromDate->copy()->addYears(3),
            default => $fromDate->copy()->addMonth(),
        };
    }

    /**
     * Cambia el ciclo de facturación (Upselling)
     */
    public function changeBillingCycle(Service $service, string $newCycle, bool $silentMode = false): void
    {
        $service->update([
            'billing_cycle' => $newCycle,
        ]);

        // Se generará una factura por el diferencial o nuevo periodo
        // Esto se maneja en el InvoiceService
    }
}
