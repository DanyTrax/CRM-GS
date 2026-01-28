<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Client;
use App\Models\Service;
use App\Models\ExchangeRate;
use App\Models\Setting;
use Carbon\Carbon;

class InvoiceService
{
    /**
     * Genera el número de factura consecutivo
     */
    public function generateInvoiceNumber(): string
    {
        $prefix = Setting::get('invoice_prefix', 'INV');
        $lastInvoice = Invoice::withTrashed()
            ->orderBy('consecutive_number', 'desc')
            ->first();
        
        $nextNumber = $lastInvoice 
            ? $lastInvoice->consecutive_number + 1 
            : (int) Setting::get('invoice_start_number', 1);
        
        return $prefix . '-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Crea una factura
     */
    public function createInvoice(array $data, bool $silentMode = false): Invoice
    {
        $invoice = Invoice::create([
            'client_id' => $data['client_id'],
            'service_id' => $data['service_id'] ?? null,
            'invoice_number' => $this->generateInvoiceNumber(),
            'prefix' => Setting::get('invoice_prefix', 'INV'),
            'consecutive_number' => $this->getNextConsecutiveNumber(),
            'document_type' => $data['document_type'] ?? 'invoice',
            'issue_date' => $data['issue_date'] ?? Carbon::now(),
            'due_date' => $data['due_date'],
            'subtotal' => $data['subtotal'],
            'tax_amount' => $data['tax_amount'] ?? 0,
            'total' => $data['total'],
            'currency' => $data['currency'] ?? 'COP',
            'status' => $data['status'] ?? 'pending',
            'notes' => $data['notes'] ?? null,
            'dian_resolution' => $data['dian_resolution'] ?? Setting::get('dian_resolution'),
        ]);

        // Si la factura es en USD, calcular conversión
        if ($invoice->currency === 'USD') {
            $exchangeRate = ExchangeRate::getActiveRate();
            $spread = Setting::get('trm_spread', 3);
            
            if ($exchangeRate) {
                $invoice->update([
                    'exchange_rate' => $exchangeRate,
                    'spread_percentage' => $spread,
                ]);
            }
        }

        // Disparar evento de correo si no es modo silencioso
        if (!$silentMode) {
            // El correo se enviará a través de la cola
            // dispatch(new SendInvoiceEmail($invoice));
        }

        return $invoice;
    }

    /**
     * Obtiene el siguiente número consecutivo
     */
    protected function getNextConsecutiveNumber(): int
    {
        $lastInvoice = Invoice::withTrashed()
            ->orderBy('consecutive_number', 'desc')
            ->first();
        
        return $lastInvoice 
            ? $lastInvoice->consecutive_number + 1 
            : (int) Setting::get('invoice_start_number', 1);
    }

    /**
     * Convierte monto USD a COP para envío a Bold
     */
    public function convertToCOPForBold(float $usdAmount): array
    {
        $exchangeRate = ExchangeRate::getActiveRate();
        $spread = Setting::get('trm_spread', 3);
        
        if (!$exchangeRate) {
            throw new \Exception('No hay tasa de cambio disponible');
        }

        $rateWithSpread = $exchangeRate * (1 + ($spread / 100));
        $copAmount = $usdAmount * $rateWithSpread;

        return [
            'usd_amount' => $usdAmount,
            'cop_amount' => round($copAmount, 2),
            'exchange_rate' => $exchangeRate,
            'spread_percentage' => $spread,
            'rate_with_spread' => $rateWithSpread,
        ];
    }
}
