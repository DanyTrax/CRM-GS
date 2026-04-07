<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Concerns\HasSettingsSubNavigation;
use App\Filament\Pages\Concerns\PersistsSystemSettings;
use App\Models\Setting;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class SettingsBranding extends Page implements HasForms
{
    use InteractsWithForms;
    use HasSettingsSubNavigation;
    use PersistsSystemSettings;

    protected static string $view = 'filament.pages.settings';

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationLabel = 'Marca y Logos';

    protected static ?string $title = 'Marca y Logos';

    protected static ?string $navigationGroup = 'Configuración';

    protected static ?int $navigationSort = 3;

    protected static ?string $slug = 'settings/marca';

    public ?array $data = [];

    public function mount(): void
    {
        try {
            $this->form->fill([
                'company_logo_light' => Setting::get('company_logo_light', null),
                'company_logo_dark' => Setting::get('company_logo_dark', null),
                'company_favicon' => Setting::get('company_favicon', null),
            ]);
        } catch (\Exception $e) {
            $this->form->fill([
                'company_logo_light' => null,
                'company_logo_dark' => null,
                'company_favicon' => null,
            ]);
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Branding')
                    ->schema([
                        Forms\Components\FileUpload::make('company_logo_light')
                            ->label('Logo para Tema Claro')
                            ->image()
                            ->directory('settings/logos')
                            ->visibility('public')
                            ->imageEditor()
                            ->helperText('Logo en tema claro (login y panel).')
                            ->columnSpan(1),

                        Forms\Components\FileUpload::make('company_logo_dark')
                            ->label('Logo para Tema Oscuro')
                            ->image()
                            ->directory('settings/logos')
                            ->visibility('public')
                            ->imageEditor()
                            ->helperText('Logo en tema oscuro (login y panel).')
                            ->columnSpan(1),

                        Forms\Components\FileUpload::make('company_favicon')
                            ->label('Icono del Sistema (Favicon)')
                            ->image()
                            ->directory('settings/logos')
                            ->visibility('public')
                            ->imageEditor()
                            ->acceptedFileTypes(['image/png', 'image/x-icon', 'image/vnd.microsoft.icon'])
                            ->helperText('Icono de la pestaña del navegador. Recomendado: PNG o ICO 32×32 o 16×16.')
                            ->columnSpanFull(),
                    ])->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        try {
            $data = $this->form->getState();
            $this->persistSettings($data, array_fill_keys(array_keys($data), 'string'));

            if (isset($data['company_logo_light']) || isset($data['company_logo_dark'])) {
                Filament::getCurrentPanel()?->brandLogo(
                    fn () => ($path = Setting::get('company_logo_light')) ? asset('storage/' . $path) : null
                );
            }

            Notification::make()
                ->title('Marca guardada')
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
