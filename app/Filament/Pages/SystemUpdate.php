<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Process;

class SystemUpdate extends Page
{
    protected static string $view = 'filament.pages.system-update';

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';

    protected static ?string $navigationLabel = 'Actualización del Sistema';

    protected static ?string $title = 'Actualización del Sistema (Git)';

    protected static ?string $navigationGroup = 'Configuración';

    protected static ?int $navigationSort = 99;

    protected static ?string $slug = 'settings/actualizacion';

    /** @var array<string, string|null> */
    public array $gitInfo = [];

    public function mount(): void
    {
        $this->refreshGitInfo();
    }

    public static function canAccess(): bool
    {
        if (! config('services.web_deploy.enabled', true)) {
            return false;
        }

        $user = auth()->user();

        return $user !== null && $user->isSuperAdmin();
    }

    public static function shouldRegisterNavigation(array $parameters = []): bool
    {
        return static::canAccess();
    }

    public function refreshGitInfo(): void
    {
        $base = base_path();

        if (! is_dir($base . DIRECTORY_SEPARATOR . '.git')) {
            $this->gitInfo = [
                'short' => null,
                'full' => null,
                'date' => null,
                'branch' => null,
                'error' => 'Este directorio no parece ser un repositorio Git (.git no encontrado).',
            ];

            return;
        }

        $short = $this->gitCommand('git rev-parse --short HEAD', $base);
        $full = $this->gitCommand('git rev-parse HEAD', $base);
        $date = $this->gitCommand('git log -1 --format=%cI', $base);
        $branch = $this->gitCommand('git branch --show-current', $base);

        $this->gitInfo = [
            'short' => $short['ok'] ? trim($short['out']) : null,
            'full' => $full['ok'] ? trim($full['out']) : null,
            'date' => $date['ok'] ? trim($date['out']) : null,
            'branch' => $branch['ok'] ? trim($branch['out']) : null,
            'error' => null,
        ];
    }

    /**
     * @return array{ok: bool, out: string}
     */
    protected function gitCommand(string $command, string $path): array
    {
        try {
            $result = Process::path($path)
                ->timeout(60)
                ->run($command);

            return [
                'ok' => $result->successful(),
                'out' => $result->successful() ? $result->output() : ($result->errorOutput() ?: $result->output()),
            ];
        } catch (\Throwable $e) {
            return ['ok' => false, 'out' => $e->getMessage()];
        }
    }

    /**
     * @return array{ok: bool, output: string}
     */
    protected function runGit(array $arguments): array
    {
        try {
            $result = Process::path(base_path())
                ->timeout(300)
                ->run(array_merge(['git'], $arguments));

            $text = trim($result->output() . ($result->errorOutput() ? "\n" . $result->errorOutput() : ''));

            return [
                'ok' => $result->successful(),
                'output' => $text !== '' ? $text : ($result->successful() ? 'OK' : 'Error sin salida'),
            ];
        } catch (\Throwable $e) {
            return ['ok' => false, 'output' => $e->getMessage()];
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('gitPull')
                ->label('Git pull (rama actual)')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Actualizar código desde Git')
                ->modalDescription('Se ejecutará `git pull` en la rama actual. Haz un respaldo antes de continuar.')
                ->action(fn () => $this->executeGitPullCurrent()),

            Action::make('gitPullOriginMain')
                ->label('Git pull origin main')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading('Actualizar desde origin/main')
                ->modalDescription('Se ejecutará `git pull origin main`. Haz un respaldo antes de continuar.')
                ->action(fn () => $this->executeGitPullOriginMain()),
        ];
    }

    public function executeGitPullCurrent(): void
    {
        $r = $this->runGit(['pull']);
        $this->afterShellCommand($r, 'git pull');
    }

    public function executeGitPullOriginMain(): void
    {
        $r = $this->runGit(['pull', 'origin', 'main']);
        $this->afterShellCommand($r, 'git pull origin main');
    }

    /**
     * @param  array{ok: bool, output: string}  $result
     */
    protected function afterShellCommand(array $result, string $label): void
    {
        $this->refreshGitInfo();

        if ($result['ok']) {
            Notification::make()
                ->title($label . ' completado')
                ->body($result['output'])
                ->success()
                ->duration(15000)
                ->send();
        } else {
            Notification::make()
                ->title('Error: ' . $label)
                ->body($result['output'])
                ->danger()
                ->duration(20000)
                ->send();
        }
    }

    protected function runArtisanSafe(string $command, array $parameters = []): void
    {
        try {
            Artisan::call($command, $parameters);
            $output = trim(Artisan::output());

            Notification::make()
                ->title('Artisan: ' . $command)
                ->body($output !== '' ? $output : 'Comando ejecutado.')
                ->success()
                ->duration(12000)
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Error en Artisan')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function artisanViewClear(): void
    {
        $this->runArtisanSafe('view:clear');
    }

    public function artisanCacheClear(): void
    {
        $this->runArtisanSafe('cache:clear');
    }

    public function artisanViewAndCacheClear(): void
    {
        $this->runArtisanSafe('view:clear');
        $this->runArtisanSafe('cache:clear');
    }

    public function artisanConfigClear(): void
    {
        $this->runArtisanSafe('config:clear');
    }

    public function artisanRouteClear(): void
    {
        $this->runArtisanSafe('route:clear');
    }

    public function artisanOptimizeClear(): void
    {
        $this->runArtisanSafe('optimize:clear');
    }

    public function artisanMigrateForce(): void
    {
        $this->runArtisanSafe('migrate', ['--force' => true]);
    }
}
