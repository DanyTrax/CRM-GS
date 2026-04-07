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

class SettingsSecurity extends Page implements HasForms
{
    use InteractsWithForms;
    use PersistsSystemSettings;

    protected static string $view = 'filament.pages.settings';

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationLabel = 'Seguridad';

    protected static ?string $title = 'Seguridad de Sesión';

    protected static ?string $navigationGroup = 'Configuración';

    protected static ?int $navigationSort = 4;

    protected static ?string $slug = 'settings/seguridad';

    public ?array $data = [];

    public function mount(): void
    {
        try {
            $this->form->fill([
                'session_timeout' => Setting::get('session_timeout', 10),
            ]);
        } catch (\Exception $e) {
            $this->form->fill(['session_timeout' => 10]);
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Seguridad')
                    ->schema([
                        Forms\Components\TextInput::make('session_timeout')
                            ->label('Tiempo de Inactividad (minutos)')
                            ->numeric()
                            ->required()
                            ->default(10)
                            ->minValue(1)
                            ->maxValue(120)
                            ->suffix('min')
                            ->helperText('Tiempo máximo de inactividad antes de cerrar sesión. Entre 1 y 120 minutos.')
                            ->columnSpanFull(),

                        Forms\Components\Placeholder::make('session_info')
                            ->label('Configuración actual')
                            ->content(fn (Forms\Get $get) => $get('session_timeout') . ' minutos de inactividad')
                            ->columnSpanFull(),

                        Forms\Components\Placeholder::make('session_description')
                            ->label('')
                            ->content('La sesión se cerrará si no hay actividad (ratón, teclado, clics, scroll).')
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        try {
            $data = $this->form->getState();
            $this->persistSettings($data, ['session_timeout' => 'integer']);

            Notification::make()
                ->title('Seguridad guardada')
                ->body('Reinicia sesión o espera al siguiente despliegue para aplicar límites en el servidor si aplica.')
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
