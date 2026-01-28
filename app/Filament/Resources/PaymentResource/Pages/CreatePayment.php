<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePayment extends CreateRecord
{
    protected static string $resource = PaymentResource::class;
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Si el método es Bold y no tiene transaction_id, generar uno temporal
        if ($data['method'] === 'Bold' && empty($data['transaction_id'])) {
            $data['transaction_id'] = 'BOLD-' . now()->format('YmdHis') . '-' . uniqid();
        }
        
        // Si el método no es Bold y no tiene approved_at, establecerlo
        if ($data['method'] !== 'Bold' && empty($data['approved_at'])) {
            $data['approved_at'] = now();
        }
        
        return $data;
    }
}
