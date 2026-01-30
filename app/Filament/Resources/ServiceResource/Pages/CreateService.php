<?php

namespace App\Filament\Resources\ServiceResource\Pages;

use App\Filament\Resources\ServiceResource;
use App\Models\Product;
use App\Models\Service;
use App\Models\Alert;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

class CreateService extends CreateRecord
{
    protected static string $resource = ServiceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Si se crea desde productos, crear múltiples servicios
        if (isset($data['creation_mode']) && $data['creation_mode'] === 'products' && isset($data['product_ids'])) {
            $this->createServicesFromProducts($data);
            // Retornar datos vacíos para evitar crear el servicio manual
            return [];
        }

        // Creación manual normal
        unset($data['creation_mode'], $data['product_ids']);
        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        // Si se crearon servicios desde productos, no crear nada aquí
        if (isset($data['creation_mode']) && $data['creation_mode'] === 'products') {
            return new Service(); // Retornar un modelo vacío
        }

        return parent::handleRecordCreation($data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function createServicesFromProducts(array $data): void
    {
        $productIds = $data['product_ids'] ?? [];
        $clientId = $data['client_id'];
        $status = $data['status'] ?? 'activo';
        $createdCount = 0;

        foreach ($productIds as $productId) {
            $product = Product::find($productId);
            if (!$product) {
                continue;
            }

            // Calcular fecha de vencimiento
            $expirationDate = $product->calculateExpirationDate(now());

            // Calcular billing_cycle
            $billingCycle = 0;
            if ($product->type === 'recurring') {
                if ($product->duration_unit === 'months') {
                    $billingCycle = $product->duration_value;
                } elseif ($product->duration_unit === 'years') {
                    $billingCycle = $product->duration_value * 12;
                } else {
                    $billingCycle = 1;
                }
            }

            // Crear servicio
            $service = Service::create([
                'client_id' => $clientId,
                'product_id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'type' => $product->type === 'recurring' ? 'recurrente' : 'unico',
                'currency' => $product->currency,
                'price' => $product->price,
                'tax_enabled' => $product->tax_enabled,
                'tax_percentage' => $product->tax_percentage,
                'billing_cycle' => $billingCycle,
                'next_due_date' => $expirationDate->toDateString(),
                'status' => $status,
            ]);

            // Si el producto es de tipo recurring, generar alerta de vencimiento
            if ($product->type === 'recurring' && $service->type === 'recurrente') {
                // Generar alerta 7 días antes del vencimiento
                Alert::createServiceExpiringAlert($service, 7);
            }

            $createdCount++;
        }

        if ($createdCount > 0) {
            Notification::make()
                ->title('Servicios creados exitosamente')
                ->body("Se crearon {$createdCount} servicio(s) desde los productos seleccionados.")
                ->success()
                ->send();
        }
    }
}
