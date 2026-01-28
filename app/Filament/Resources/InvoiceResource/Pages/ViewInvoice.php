<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewInvoice extends ViewRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('pdf')
                ->label('Descargar PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->url(fn () => route('admin.invoices.pdf', $this->record))
                ->openUrlInNewTab()
                ->color('success'),
            
            Actions\Action::make('mark_paid')
                ->label('Marcar como Pagada')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => $this->record->status !== 'pagada')
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->markAsPaid();
                    $this->notify('success', 'Factura marcada como pagada');
                }),
            
            Actions\EditAction::make(),
        ];
    }
}
