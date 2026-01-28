<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Models\Setting;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Backup diario a Google Drive (2:00 AM)
        $schedule->command('backup:google-drive')
            ->dailyAt('02:00')
            ->withoutOverlapping();

        // Actualizar fecha de última ejecución del scheduler
        $schedule->call(function () {
            Setting::set('last_schedule_run', now()->toDateTimeString(), 'string');
        })->everyMinute();

        // Limpiar trabajos fallidos antiguos (semanal)
        $schedule->command('queue:prune-failed')->weekly();

        // Generar facturas automáticas para servicios que vencen pronto
        $schedule->call(function () {
            \Log::info('Ejecutando generación automática de facturas...');
            // TODO: Implementar lógica de generación de facturas
        })->daily();

        // Generar alertas automáticas (servicios próximos a vencer, facturas vencidas, etc.)
        $schedule->command('alerts:generate')
            ->daily()
            ->at('08:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
