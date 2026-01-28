<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Si no se proporcionó invoice_number, generarlo automáticamente
        if (empty($data['invoice_number'])) {
            $template = $data['pdf_template'] ?? 'legal';
            $data['invoice_number'] = \App\Models\Invoice::generateInvoiceNumber($template);
        }
        
        return $data;
    }
}
