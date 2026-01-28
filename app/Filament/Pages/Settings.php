<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Notifications\Notification;

class Settings extends Page implements HasForms
{
    use InteractsWithForms;
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    
    protected static string $view = 'filament.pages.settings';
    
    protected static ?string $navigationLabel = 'Configuración';
    
    protected static ?string $title = 'Configuración del Sistema';
    
    protected static ?string $navigationGroup = 'Configuración';
    
    protected static ?int $navigationSort = 1;
    
    protected static ?string $slug = 'settings';
    
    public ?array $data = [];
    
    public function mount(): void
    {
        try {
            $this->form->fill([
                'trm_base' => Setting::get('trm_base', 4000),
                'bold_spread_percentage' => Setting::get('bold_spread_percentage', 3),
                'bold_webhook_secret' => Setting::get('bold_webhook_secret', ''),
                'backup_enabled' => Setting::get('backup_enabled', true),
                'google_drive_folder_id' => Setting::get('google_drive_folder_id', ''),
            ]);
        } catch (\Exception $e) {
            // Si hay error al cargar settings, usar valores por defecto
            $this->form->fill([
                'trm_base' => 4000,
                'bold_spread_percentage' => 3,
                'bold_webhook_secret' => '',
                'backup_enabled' => true,
                'google_drive_folder_id' => '',
            ]);
        }
    }
    
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Configuración de Moneda')
                    ->schema([
                        Forms\Components\TextInput::make('trm_base')
                            ->label('TRM Base (USD a COP)')
                            ->numeric()
                            ->required()
                            ->helperText('Tasa de cambio base para conversión USD a COP'),
                        
                        Forms\Components\TextInput::make('bold_spread_percentage')
                            ->label('Spread Bold (%)')
                            ->numeric()
                            ->required()
                            ->helperText('Porcentaje de spread para conversión Bold (TRM + Spread)'),
                    ])->columns(2),
                
                Forms\Components\Section::make('Configuración de Bold')
                    ->schema([
                        Forms\Components\TextInput::make('bold_webhook_secret')
                            ->label('Secret Key de Webhook')
                            ->password()
                            ->maxLength(255)
                            ->helperText('Secret key para validar webhooks de Bold'),
                    ]),
                
                Forms\Components\Section::make('Configuración de Backups')
                    ->schema([
                        Forms\Components\Toggle::make('backup_enabled')
                            ->label('Habilitar Backups Automáticos')
                            ->helperText('Activar backups automáticos a Google Drive'),
                        
                        Forms\Components\TextInput::make('google_drive_folder_id')
                            ->label('ID de Carpeta en Google Drive')
                            ->maxLength(255)
                            ->helperText('ID de la carpeta en Google Drive para almacenar backups'),
                    ])->columns(2),
            ])
            ->statePath('data');
    }
    
    public function save(): void
    {
        try {
            $data = $this->form->getState();
            
            foreach ($data as $key => $value) {
                $type = match($key) {
                    'trm_base', 'bold_spread_percentage' => 'integer',
                    'backup_enabled' => 'boolean',
                    default => 'string',
                };
                
                Setting::set($key, $value, $type);
            }
            
            Notification::make()
                ->title('Configuración guardada exitosamente')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error al guardar configuración')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
    
    protected function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('save')
                ->label('Guardar Configuración')
                ->submit('save'),
        ];
    }
}
