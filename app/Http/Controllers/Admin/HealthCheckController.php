<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CronJobLog;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\File;

class HealthCheckController extends Controller
{
    public function index()
    {
        // Verificar última ejecución de cron
        $lastCron = CronJobLog::where('job_name', 'schedule:run')
            ->latest('started_at')
            ->first();

        $cronStatus = 'danger';
        $cronMessage = 'No se ha ejecutado recientemente';
        
        if ($lastCron) {
            $minutesAgo = $lastCron->started_at->diffInMinutes(now());
            if ($minutesAgo < 5) {
                $cronStatus = 'success';
                $cronMessage = "Ejecutado hace {$minutesAgo} minutos";
            } elseif ($minutesAgo < 15) {
                $cronStatus = 'warning';
                $cronMessage = "Ejecutado hace {$minutesAgo} minutos";
            }
        }

        // Verificar trabajos fallidos
        $failedJobs = Queue::size('failed');
        $queueStatus = $failedJobs > 0 ? 'danger' : 'success';
        $queueMessage = $failedJobs > 0 
            ? "{$failedJobs} trabajos fallidos" 
            : 'Sin trabajos fallidos';

        // Logs recientes
        $logFile = storage_path('logs/laravel.log');
        $logLines = [];
        if (File::exists($logFile)) {
            $logContent = File::get($logFile);
            $lines = explode("\n", $logContent);
            $logLines = array_slice(array_reverse($lines), 0, 100);
        }

        return view('admin.health.index', compact(
            'cronStatus',
            'cronMessage',
            'queueStatus',
            'queueMessage',
            'logLines'
        ));
    }
}
