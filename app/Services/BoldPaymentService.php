<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Invoice;
use App\Models\Service;
use App\Models\ServiceRenewal;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BoldPaymentService
{
    /**
     * Procesar webhook de Bold
     * 
     * Flujo: Approved -> Pagar Factura -> Renovar Servicio
     * 
     * @param array $webhookData Datos del webhook
     * @param string $signature Firma del webhook
     * @return array
     */
    public function processWebhook(array $webhookData, string $signature): array
    {
        try {
            // Validar firma del webhook
            if (!$this->validateSignature($webhookData, $signature)) {
                Log::warning('Bold Webhook: Firma inválida', ['data' => $webhookData]);
                return ['success' => false, 'message' => 'Firma inválida'];
            }

            // Verificar que el estado sea "approved"
            if (!isset($webhookData['status']) || $webhookData['status'] !== 'approved') {
                return ['success' => false, 'message' => 'Estado no es approved'];
            }

            // Buscar el pago por transaction_id o reference
            $payment = Payment::where('bold_transaction_id', $webhookData['transaction_id'] ?? null)
                ->orWhere('bold_reference', $webhookData['reference'] ?? null)
                ->first();

            if (!$payment) {
                Log::warning('Bold Webhook: Pago no encontrado', ['data' => $webhookData]);
                return ['success' => false, 'message' => 'Pago no encontrado'];
            }

            // Si ya está aprobado, no hacer nada
            if ($payment->isApproved()) {
                return ['success' => true, 'message' => 'Pago ya estaba aprobado'];
            }

            // Aprobar el pago (marca la factura como pagada)
            $systemUser = User::whereHas('roles', function($q) {
                $q->where('slug', 'super-admin');
            })->first() ?? User::first();

            $payment->approve($systemUser);
            
            // Actualizar datos de Bold
            $payment->update([
                'bold_response' => $webhookData,
                'bold_signature' => $signature,
            ]);

            // Si hay factura asociada y servicio, renovar
            if ($payment->invoice && $payment->invoice->service) {
                $this->renewServiceAfterPayment($payment->invoice->service, $payment);
            }

            Log::info('Bold Webhook: Pago aprobado y servicio renovado', [
                'payment_id' => $payment->id,
                'invoice_id' => $payment->invoice_id,
            ]);

            return ['success' => true, 'message' => 'Pago aprobado y servicio renovado'];
        } catch (\Exception $e) {
            Log::error('Bold Webhook: Error procesando webhook', [
                'error' => $e->getMessage(),
                'data' => $webhookData,
            ]);
            
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Renovar servicio después del pago
     */
    protected function renewServiceAfterPayment(Service $service, Payment $payment): ServiceRenewal
    {
        if ($service->type !== 'recurrent') {
            return null;
        }

        // Renovar el servicio (usa fecha de vencimiento anterior, no fecha actual)
        $renewal = $service->renew(null, Carbon::parse($payment->approved_at));

        // Actualizar la renovación con el pago y factura
        $renewal->update([
            'invoice_id' => $payment->invoice_id,
            'payment_id' => $payment->id,
        ]);

        return $renewal;
    }

    /**
     * Validar firma del webhook de Bold
     * 
     * @param array $data Datos del webhook
     * @param string $signature Firma recibida
     * @return bool
     */
    protected function validateSignature(array $data, string $signature): bool
    {
        $secretKey = config('services.bold.webhook_secret');
        
        if (!$secretKey) {
            Log::warning('Bold Webhook: Secret key no configurado');
            return false;
        }

        // Construir el payload para verificar
        $payload = json_encode($data);
        $expectedSignature = hash_hmac('sha256', $payload, $secretKey);

        return hash_equals($expectedSignature, $signature);
    }
}
