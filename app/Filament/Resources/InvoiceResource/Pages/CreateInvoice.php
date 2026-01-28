<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;
    
    public function mount(): void
    {
        parent::mount();
        
        // Si viene desde un servicio, pre-llenar datos
        $serviceId = request()->get('service_id');
        if ($serviceId) {
            $service = \App\Models\Service::find($serviceId);
            if ($service) {
                $this->form->fill([
                    'client_id' => $service->client_id,
                    'total_amount' => $service->price,
                    'currency' => $service->currency,
                    'concept' => "Facturación de servicio: {$service->name}",
                ]);
                
                // Si es USD, calcular TRM automáticamente con tolerancia
                if ($service->currency === 'USD') {
                    $trmBase = (float) \App\Services\ExchangeRateService::getTRM();
                    $spread = \App\Models\Setting::get('bold_spread_percentage', 3);
                    $toleranceType = \App\Models\Setting::get('exchange_tolerance_type', 'percentage');
                    $toleranceValue = \App\Models\Setting::get('exchange_tolerance_value', 0);
                    
                    $trmWithSpread = $trmBase * (1 + ($spread / 100));
                    
                    // Aplicar tolerancia
                    if ($toleranceType === 'percentage') {
                        $finalRate = $trmWithSpread * (1 + ($toleranceValue / 100));
                    } else {
                        $finalRate = $trmWithSpread + $toleranceValue;
                    }
                    
                    // Calcular precio en COP con redondeo
                    $priceInCOP = $service->getPriceInCOP();
                    
                    $this->form->fill([
                        'trm_snapshot' => round($finalRate, 4),
                        'total_amount' => $priceInCOP,
                        'currency' => 'COP', // Siempre facturar en COP
                    ]);
                } else {
                    // Si es COP, usar precio con impuesto
                    $this->form->fill([
                        'total_amount' => $service->getPriceWithTax(),
                    ]);
                }
            }
        }
    }
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Si no se proporcionó invoice_number, generarlo automáticamente
        if (empty($data['invoice_number'])) {
            $template = $data['pdf_template'] ?? 'legal';
            $data['invoice_number'] = \App\Models\Invoice::generateInvoiceNumber($template);
        }
        
        // Si es USD y no tiene TRM, calcularla
        if ($data['currency'] === 'USD' && empty($data['trm_snapshot'])) {
            $trmBase = \App\Models\Setting::get('trm_base', 4000);
            $spread = \App\Models\Setting::get('bold_spread_percentage', 3);
            $data['trm_snapshot'] = round($trmBase * (1 + ($spread / 100)), 4);
        }
        
        return $data;
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
    
    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Factura creada')
            ->body('La factura ha sido creada exitosamente.');
    }
}
