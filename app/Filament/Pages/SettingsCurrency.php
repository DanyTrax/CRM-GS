<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Concerns\PersistsSystemSettings;
use App\Models\Setting;
use App\Services\ExchangeRateService;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class SettingsCurrency extends Page implements HasForms
{
    use InteractsWithForms;
    use PersistsSystemSettings;

    protected static string $view = 'filament.pages.settings';

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationLabel = 'Configuración de Moneda';

    protected static ?string $title = 'Configuración de Moneda';

    protected static ?string $navigationGroup = 'Configuración';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'settings/moneda';

    public ?array $data = [];

    public function mount(): void
    {
        try {
            $trmAutoEnabled = Setting::get('trm_auto_enabled', false);
            $trmBase = Setting::get('trm_base', 4000);

            if ($trmAutoEnabled) {
                $autoTRM = ExchangeRateService::getAutomaticTRM();
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
            ]);
        } catch (\Exception $e) {
            $this->form->fill([
                'trm_auto_enabled' => false,
                'trm_base' => 4000,
                'bold_spread_percentage' => 3,
                'exchange_tolerance_type' => 'percentage',
                'exchange_tolerance_value' => 0,
                'exchange_rounding' => 'up',
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
                                    $autoTRM = ExchangeRateService::getAutomaticTRM();
                                    if ($autoTRM) {
                                        $set('trm_base', $autoTRM);
                                        Notification::make()
                                            ->title('TRM obtenida automáticamente')
                                            ->body('Tasa de cambio: ' . number_format($autoTRM, 2) . ' COP')
                                            ->success()
                                            ->send();
                                    } else {
                                        Notification::make()
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
                            ->helperText(fn ($get) => $get('trm_auto_enabled')
                                ? 'TRM obtenida automáticamente (se actualiza cada hora)'
                                : 'Tasa de cambio manual para conversión USD a COP (ej: 3659.29)'),

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
                            ->helperText(fn ($get) => $get('exchange_tolerance_type') === 'percentage'
                                ? 'Porcentaje adicional a aplicar sobre TRM + Spread'
                                : 'Valor fijo en COP a sumar a TRM + Spread'),

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
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        try {
            $data = $this->form->getState();

            $this->persistSettings($data, [
                'trm_base' => 'decimal',
                'bold_spread_percentage' => 'decimal',
                'exchange_tolerance_value' => 'decimal',
                'trm_auto_enabled' => 'boolean',
                'exchange_tolerance_type' => 'string',
                'exchange_rounding' => 'string',
            ]);

            if (! empty($data['trm_auto_enabled'])) {
                $autoTRM = ExchangeRateService::getAutomaticTRM();
                if ($autoTRM) {
                    Setting::set('trm_base', $autoTRM, 'decimal', 'TRM obtenida automáticamente');
                }
            }

            Notification::make()
                ->title('Configuración de moneda guardada')
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
