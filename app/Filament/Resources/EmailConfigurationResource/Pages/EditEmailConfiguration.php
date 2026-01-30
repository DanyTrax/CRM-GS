<?php

namespace App\Filament\Resources\EmailConfigurationResource\Pages;

use App\Filament\Resources\EmailConfigurationResource;
use App\Models\EmailConfiguration;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditEmailConfiguration extends EditRecord
{
    protected static string $resource = EmailConfigurationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('authorize_zoho')
                ->label('Autorizar con Zoho y Generar Refresh Token Automáticamente')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => $this->record->provider === 'zoho' && $this->record->zoho_client_id && $this->record->zoho_client_secret)
                ->requiresConfirmation()
                ->modalHeading('Autorizar con Zoho')
                ->modalDescription('Serás redirigido a Zoho para autorizar la aplicación. Asegúrate de usar la cuenta: ' . ($this->record->from_email ?? 'soporte@acdoblevia.com'))
                ->action(function () {
                    return redirect()->route('zoho.oauth.authorize', ['config_id' => $this->record->id]);
                }),
            
            Actions\Action::make('clear_zoho_token')
                ->label('Limpiar Refresh Token')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->visible(fn () => $this->record->provider === 'zoho' && $this->record->zoho_refresh_token)
                ->requiresConfirmation()
                ->modalHeading('Limpiar Refresh Token')
                ->modalDescription('¿Estás seguro de que deseas limpiar el Refresh Token? Deberás autorizar nuevamente con Zoho.')
                ->action(function () {
                    $this->record->update([
                        'zoho_refresh_token' => null,
                        'zoho_access_token' => null,
                        'zoho_token_expires_at' => null,
                    ]);
                    
                    Notification::make()
                        ->title('Refresh Token limpiado')
                        ->body('El Refresh Token ha sido limpiado exitosamente.')
                        ->success()
                        ->send();
                    
                    $this->redirect($this->getResource()::getUrl('edit', ['record' => $this->record]));
                }),
            
            Actions\DeleteAction::make(),
        ];
    }
    
    protected function afterSave(): void
    {
        // Inyectar JavaScript para el botón de autorización
        $this->dispatch('zoho-auth-button-mounted', [
            'configId' => $this->record->id,
            'authorizeUrl' => route('zoho.oauth.authorize', ['config_id' => $this->record->id]),
        ]);
    }
}
