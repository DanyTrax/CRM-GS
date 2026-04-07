<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Concerns\PersistsSystemSettings;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class SettingsBillingContact extends Page implements HasForms
{
    use InteractsWithForms;
    use PersistsSystemSettings;

    protected static string $view = 'filament.pages.settings';

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationLabel = 'Facturación y Contacto';

    protected static ?string $title = 'Información de Facturación y Contacto';

    protected static ?string $navigationGroup = 'Configuración';

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'settings/facturacion-contacto';

    public ?array $data = [];

    public function mount(): void
    {
        try {
            $this->form->fill([
                'company_name' => Setting::get('company_name', 'DOWGROUP'),
                'company_tax_id' => Setting::get('company_tax_id', ''),
                'company_email' => Setting::get('company_email', ''),
                'company_phone' => Setting::get('company_phone', ''),
                'company_address' => Setting::get('company_address', ''),
                'company_website' => Setting::get('company_website', ''),
            ]);
        } catch (\Exception $e) {
            $this->form->fill([
                'company_name' => 'DOWGROUP',
                'company_tax_id' => '',
                'company_email' => '',
                'company_phone' => '',
                'company_address' => '',
                'company_website' => '',
            ]);
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información de Facturación y Contacto')
                    ->schema([
                        Forms\Components\TextInput::make('company_name')
                            ->label('Nombre de la Empresa')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('company_tax_id')
                            ->label('NIT')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('company_email')
                            ->label('Email de Contacto')
                            ->email()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('company_phone')
                            ->label('Teléfono')
                            ->tel()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('company_address')
                            ->label('Dirección')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('company_website')
                            ->label('Sitio Web')
                            ->url()
                            ->maxLength(255),
                    ])->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        try {
            $data = $this->form->getState();
            $this->persistSettings($data, array_fill_keys(array_keys($data), 'string'));

            Notification::make()
                ->title('Datos de empresa guardados')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error al guardar')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('save')
                ->label('Guardar')
                ->submit('save'),
        ];
    }
}
