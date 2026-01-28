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
            $trmAutoEnabled = Setting::get('trm_auto_enabled', false);
            $trmBase = Setting::get('trm_base', 4000);
            
            // Si está habilitada la TRM automática, intentar obtenerla
            if ($trmAutoEnabled) {
                $autoTRM = \App\Services\ExchangeRateService::getAutomaticTRM();
                if ($autoTRM) {
                    $trmBase = $autoTRM;
                }
            }
            
            $this->form->fill([
                'trm_auto_enabled' => $trmAutoEnabled,
                'trm_base' => $trmBase,
                'bold_spread_percentage' => Setting::get('bold_spread_percentage', 3),
                'exchange_tolerance_type' => Setting::get('exchange_tolerance_type', 'percentage'),
                'exchange_tolerance_value' => Setting::get('exchange_tolerance_value', 0),
                'exchange_rounding' => Setting::get('exchange_rounding', 'up'),
                'bold_webhook_secret' => Setting::get('bold_webhook_secret', ''),
                'backup_enabled' => Setting::get('backup_enabled', true),
                'google_drive_folder_id' => Setting::get('google_drive_folder_id', ''),
            ]);
        } catch (\Exception $e) {
            // Si hay error al cargar settings, usar valores por defecto
            $this->form->fill([
                'trm_auto_enabled' => false,
                'trm_base' => 4000,
                'bold_spread_percentage' => 3,
                'exchange_tolerance_type' => 'percentage',
                'exchange_tolerance_value' => 0,
                'exchange_rounding' => 'up',
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
                        Forms\Components\Toggle::make('trm_auto_enabled')
                            ->label('TRM Automática')
                            ->default(false)
                            ->live()
                            ->helperText('Obtener tasa de cambio automáticamente del Banco de la República de Colombia')
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    // Intentar obtener TRM automática
                                    $autoTRM = \App\Services\ExchangeRateService::getAutomaticTRM();
                                    if ($autoTRM) {
                                        $set('trm_base', $autoTRM);
                                        \Filament\Notifications\Notification::make()
                                            ->title('TRM obtenida automáticamente')
                                            ->body("Tasa de cambio: " . number_format($autoTRM, 2) . " COP")
                                            ->success()
                                            ->send();
                                    } else {
                                        \Filament\Notifications\Notification::make()
                                            ->title('No se pudo obtener TRM automática')
                                            ->body('Usando valor manual. Verifica tu conexión a internet.')
                                            ->warning()
                                            ->send();
                                    }
                                }
                            }),
                        
                        Forms\Components\TextInput::make('trm_base')
                            ->label('TRM Base (USD a COP)')
                            ->numeric()
                            ->required()
                            ->step(0.01)
                            ->disabled(fn ($get) => $get('trm_auto_enabled'))
                            ->helperText(fn ($get) => 
                                $get('trm_auto_enabled')
                                    ? 'TRM obtenida automáticamente (se actualiza cada hora)'
                                    : 'Tasa de cambio manual para conversión USD a COP (ej: 3659.29)'
                            ),
                        
                        Forms\Components\TextInput::make('bold_spread_percentage')
                            ->label('Spread Bold (%)')
                            ->numeric()
                            ->required()
                            ->step(0.01)
                            ->helperText('Porcentaje de spread para conversión Bold (TRM + Spread)'),
                        
                        Forms\Components\Select::make('exchange_tolerance_type')
                            ->label('Tipo de Tolerancia')
                            ->options([
                                'percentage' => 'Porcentaje (%)',
                                'fixed' => 'Valor Fijo (COP)',
                            ])
                            ->default('percentage')
                            ->required()
                            ->live()
                            ->helperText('Tipo de ajuste adicional para la tasa de cambio'),
                        
                        Forms\Components\TextInput::make('exchange_tolerance_value')
                            ->label('Valor de Tolerancia')
                            ->numeric()
                            ->default(0)
                            ->step(0.01)
                            ->required()
                            ->suffix(fn ($get) => $get('exchange_tolerance_type') === 'percentage' ? '%' : 'COP')
                            ->helperText(fn ($get) => 
                                $get('exchange_tolerance_type') === 'percentage'
                                    ? 'Porcentaje adicional a aplicar sobre TRM + Spread'
                                    : 'Valor fijo en COP a sumar a TRM + Spread'
                            ),
                        
                        Forms\Components\Select::make('exchange_rounding')
                            ->label('Redondeo')
                            ->options([
                                'up' => 'Redondear hacia arriba',
                                'down' => 'Redondear hacia abajo',
                                'nearest' => 'Redondear al más cercano',
                            ])
                            ->default('up')
                            ->required()
                            ->helperText('Método de redondeo para conversión USD a COP (ej: 3659.29 → 3660)'),
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
                    'trm_base', 'bold_spread_percentage', 'exchange_tolerance_value' => 'decimal',
                    'trm_auto_enabled', 'backup_enabled' => 'boolean',
                    'exchange_tolerance_type', 'exchange_rounding' => 'string',
                    default => 'string',
                };
                
                Setting::set($key, $value, $type);
            }
            
            // Si está habilitada la TRM automática, actualizarla
            if (isset($data['trm_auto_enabled']) && $data['trm_auto_enabled']) {
                $autoTRM = \App\Services\ExchangeRateService::getAutomaticTRM();
                if ($autoTRM) {
                    Setting::set('trm_base', $autoTRM, 'decimal', 'TRM obtenida automáticamente');
                }
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
