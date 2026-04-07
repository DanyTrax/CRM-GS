<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Concerns\HasSettingsSubNavigation;
use App\Filament\Pages\Concerns\PersistsSystemSettings;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class SettingsBackups extends Page implements HasForms
{
    use InteractsWithForms;
    use HasSettingsSubNavigation;
    use PersistsSystemSettings;

    protected static string $view = 'filament.pages.settings';

    protected static ?string $navigationIcon = 'heroicon-o-cloud-arrow-up';

    protected static ?string $navigationLabel = 'Conexión Google Drive';

    protected static ?string $title = 'Configuración de Backups';

    protected static ?string $navigationGroup = 'Configuración';

    protected static ?int $navigationSort = 5;

    protected static ?string $slug = 'settings/backups';

    public ?array $data = [];

    public function mount(): void
    {
        try {
            $this->form->fill([
                'backup_enabled' => Setting::get('backup_enabled', true),
                'google_drive_folder_id' => Setting::get('google_drive_folder_id', ''),
            ]);
        } catch (\Exception $e) {
            $this->form->fill([
                'backup_enabled' => true,
                'google_drive_folder_id' => '',
            ]);
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
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
            $this->persistSettings($data, [
                'backup_enabled' => 'boolean',
                'google_drive_folder_id' => 'string',
            ]);

            Notification::make()
                ->title('Backups guardado')
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
