<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Service;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Alert;
use Carbon\Carbon;

class GenerateAlerts extends Command
{
    protected $signature = 'alerts:generate';
    protected $description = 'Generar alertas automáticas para servicios próximos a vencer, facturas vencidas, etc.';

    public function handle()
    {
        $this->info('🔔 Generando alertas automáticas...');
        
        $alertsCreated = 0;

        // 1. Alertas para servicios próximos a vencer (7 días antes)
        $this->info('Verificando servicios próximos a vencer...');
        $servicesExpiring = Service::where('status', 'activo')
            ->where('type', 'recurrente')
            ->whereBetween('next_due_date', [
                now()->addDays(7)->toDateString(),
                now()->addDays(7)->toDateString(),
            ])
            ->get();

        foreach ($servicesExpiring as $service) {
            // Verificar si ya existe una alerta para este servicio
            $existingAlert = Alert::where('entity_type', Service::class)
                ->where('entity_id', $service->id)
                ->where('type', 'service_expiring')
                ->where('status', 'pending')
                ->first();

            if (!$existingAlert) {
                Alert::createServiceExpiringAlert($service, 7);
                $alertsCreated++;
            }
        }

        // 2. Alertas para servicios vencidos
        $this->info('Verificando servicios vencidos...');
        $servicesExpired = Service::where('status', 'activo')
            ->where('next_due_date', '<', now())
            ->get();

        foreach ($servicesExpired as $service) {
            $existingAlert = Alert::where('entity_type', Service::class)
                ->where('entity_id', $service->id)
                ->where('type', 'service_expired')
                ->where('status', 'pending')
                ->first();

            if (!$existingAlert) {
                Alert::create([
                    'name' => "Servicio vencido: {$service->name}",
                    'type' => 'service_expired',
                    'entity_type' => Service::class,
                    'entity_id' => $service->id,
                    'message' => "El servicio '{$service->name}' del cliente '{$service->client->company_name}' está vencido desde el {$service->next_due_date->format('d/m/Y')}",
                    'priority' => 'urgent',
                    'status' => 'pending',
                    'trigger_date' => now(),
                    'metadata' => [
                        'service_id' => $service->id,
                        'client_id' => $service->client_id,
                        'due_date' => $service->next_due_date->toDateString(),
                    ],
                ]);
                $alertsCreated++;
            }
        }

        // 3. Alertas para facturas vencidas
        $this->info('Verificando facturas vencidas...');
        $invoicesOverdue = Invoice::where('status', 'pendiente')
            ->where('due_date', '<', now())
            ->get();

        foreach ($invoicesOverdue as $invoice) {
            $existingAlert = Alert::where('entity_type', Invoice::class)
                ->where('entity_id', $invoice->id)
                ->where('type', 'invoice_overdue')
                ->where('status', 'pending')
                ->first();

            if (!$existingAlert) {
                Alert::createInvoiceOverdueAlert($invoice);
                $alertsCreated++;
            }
        }

        // 4. Alertas para pagos pendientes de aprobación (más de 24 horas)
        $this->info('Verificando pagos pendientes...');
        $paymentsPending = Payment::whereNull('approved_at')
            ->where('created_at', '<', now()->subDay())
            ->get();

        foreach ($paymentsPending as $payment) {
            $existingAlert = Alert::where('entity_type', Payment::class)
                ->where('entity_id', $payment->id)
                ->where('type', 'payment_pending')
                ->where('status', 'pending')
                ->first();

            if (!$existingAlert) {
                Alert::createPaymentPendingAlert($payment);
                $alertsCreated++;
            }
        }

        $this->info("✅ Alertas generadas: {$alertsCreated}");
        
        return 0;
    }
}
