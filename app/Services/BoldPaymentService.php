<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Service;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BoldPaymentService
{
    protected string $apiKey;
    protected string $apiSecret;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('bold.api_key');
        $this->apiSecret = config('bold.api_secret');
        $this->baseUrl = config('bold.base_url');
    }

    /**
     * Genera la URL de checkout de Bold
     */
    public function generateCheckoutUrl(Invoice $invoice): string
    {
        // Si la factura es en USD, convertir a COP
        $amount = $invoice->currency === 'USD' 
            ? $this->convertToCOP($invoice->total, $invoice->exchange_rate, $invoice->spread_percentage)
            : $invoice->total;

        $signature = $this->generateSignature([
            'invoice_id' => $invoice->id,
            'amount' => $amount,
            'currency' => 'COP',
        ]);

        $checkoutData = [
            'invoice_id' => $invoice->id,
            'amount' => $amount,
            'currency' => 'COP',
            'reference' => $invoice->invoice_number,
            'signature' => $signature,
            'success_url' => route('payment.success'),
            'cancel_url' => route('payment.cancel'),
        ];

        // Llamada a API de Bold para crear checkout
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
        ])->post($this->baseUrl . '/checkout', $checkoutData);

        if ($response->successful()) {
            return $response->json('checkout_url');
        }

        throw new \Exception('Error al generar checkout de Bold: ' . $response->body());
    }

    /**
     * Valida la firma del webhook
     */
    public function validateWebhookSignature(array $data, string $signature): bool
    {
        $expectedSignature = $this->generateSignature($data);
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Procesa el webhook de Bold
     */
    public function processWebhook(array $data): void
    {
        // Validar firma
        if (!$this->validateWebhookSignature($data, $data['signature'] ?? '')) {
            Log::error('Bold webhook: Firma inválida', $data);
            throw new \Exception('Firma de webhook inválida');
        }

        $invoiceId = $data['invoice_id'] ?? null;
        $status = $data['status'] ?? null;
        $transactionId = $data['transaction_id'] ?? null;

        if (!$invoiceId || $status !== 'approved') {
            return;
        }

        $invoice = Invoice::find($invoiceId);
        
        if (!$invoice) {
            Log::error('Bold webhook: Factura no encontrada', ['invoice_id' => $invoiceId]);
            return;
        }

        // Registrar pago
        $payment = Payment::create([
            'invoice_id' => $invoice->id,
            'service_id' => $invoice->service_id,
            'method' => 'bold',
            'status' => 'approved',
            'amount' => $data['amount'] ?? $invoice->total,
            'currency' => 'COP',
            'transaction_id' => $transactionId,
            'payment_date' => now(),
        ]);

        // Actualizar factura
        $invoice->update(['status' => 'paid']);

        // Renovar servicio si aplica
        if ($invoice->service_id) {
            app(ServiceRenewalService::class)->renewServiceFromPayment($payment);
        }

        // Enviar notificación de pago recibido
        // dispatch(new SendPaymentConfirmationEmail($payment));
    }

    /**
     * Convierte USD a COP
     */
    protected function convertToCOP(float $usdAmount, float $exchangeRate, float $spread): float
    {
        $rateWithSpread = $exchangeRate * (1 + ($spread / 100));
        return $usdAmount * $rateWithSpread;
    }

    /**
     * Genera firma de integridad
     */
    protected function generateSignature(array $data): string
    {
        ksort($data);
        $string = http_build_query($data) . $this->apiSecret;
        return hash('sha256', $string);
    }
}
