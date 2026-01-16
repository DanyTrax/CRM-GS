<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Schedule;

Schedule::command('inspire')->hourly();

// Tarea programada para verificar facturas vencidas
Schedule::call(function () {
    \App\Models\Invoice::where('status', 'pending')
        ->where('due_date', '<', now())
        ->update(['status' => 'overdue']);
})->daily();

// Tarea programada para backups diarios
Schedule::call(function () {
    app(\App\Services\BackupService::class)->createBackup();
})->dailyAt('02:00');

// Registrar ejecución de cron
Schedule::call(function () {
    \App\Models\CronJobLog::create([
        'job_name' => 'schedule:run',
        'status' => 'success',
        'started_at' => now(),
        'completed_at' => now(),
        'duration_seconds' => 0,
    ]);
})->everyMinute();
