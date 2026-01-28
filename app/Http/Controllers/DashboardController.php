<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

class DashboardController extends Controller
{
    /**
     * Dashboard principal con Health Check
     */
    public function index()
    {
        $healthStatus = $this->getHealthStatus();
        
        // Estadísticas generales
        $stats = [
            'total_clients' => \App\Models\Client::count(),
            'active_services' => \App\Models\Service::where('is_active', true)->count(),
            'pending_invoices' => \App\Models\Invoice::where('status', 'pending')->count(),
            'open_tickets' => \App\Models\Ticket::where('status', 'open')->count(),
        ];

        return view('admin.dashboard', compact('healthStatus', 'stats'));
    }

    /**
     * Panel de Salud (Health Check)
     * Verifica estado de Crons y Colas
     */
    protected function getHealthStatus(): array
    {
        $status = [
            'database' => $this->checkDatabase(),
            'queue' => $this->checkQueue(),
            'cron' => $this->checkCron(),
            'storage' => $this->checkStorage(),
            'last_backup' => $this->getLastBackupDate(),
        ];

        $status['overall'] = !in_array(false, array_column($status, 'status'));

        return $status;
    }

    protected function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();
            return ['status' => true, 'message' => 'Conectado'];
        } catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    protected function checkQueue(): array
    {
        try {
            // Verificar si la tabla de jobs existe y tiene conexión
            $jobsCount = DB::table('jobs')->count();
            $failedJobs = DB::table('failed_jobs')->count();
            
            return [
                'status' => true,
                'message' => "Jobs: {$jobsCount}, Fallidos: {$failedJobs}",
                'jobs_pending' => $jobsCount,
                'jobs_failed' => $failedJobs,
            ];
        } catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    protected function checkCron(): array
    {
        // Verificar última ejecución del scheduler
        $lastScheduleRun = \App\Models\Setting::get('last_schedule_run');
        
        if ($lastScheduleRun) {
            $lastRun = \Carbon\Carbon::parse($lastScheduleRun);
            $minutesAgo = now()->diffInMinutes($lastRun);
            
            // Si no se ha ejecutado en más de 5 minutos, podría estar fallando
            $isHealthy = $minutesAgo < 5;
            
            return [
                'status' => $isHealthy,
                'message' => $isHealthy ? "Última ejecución: {$minutesAgo} min ago" : "Última ejecución hace {$minutesAgo} min (posible fallo)",
                'last_run' => $lastRun->format('Y-m-d H:i:s'),
            ];
        }

        return ['status' => false, 'message' => 'No hay registro de ejecución'];
    }

    protected function checkStorage(): array
    {
        try {
            $totalSpace = disk_total_space(storage_path());
            $freeSpace = disk_free_space(storage_path());
            $usedSpace = $totalSpace - $freeSpace;
            $usedPercentage = ($usedSpace / $totalSpace) * 100;

            return [
                'status' => $usedPercentage < 90,
                'message' => sprintf('Uso: %.1f%%', $usedPercentage),
                'free_space_gb' => round($freeSpace / 1024 / 1024 / 1024, 2),
                'total_space_gb' => round($totalSpace / 1024 / 1024 / 1024, 2),
            ];
        } catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    protected function getLastBackupDate(): array
    {
        $lastBackup = \App\Models\Setting::get('last_backup_date');
        
        if ($lastBackup) {
            $lastBackupDate = \Carbon\Carbon::parse($lastBackup);
            $hoursAgo = now()->diffInHours($lastBackupDate);
            
            return [
                'status' => $hoursAgo < 24, // Debe hacerse backup diario
                'message' => $lastBackupDate->format('Y-m-d H:i:s'),
                'hours_ago' => $hoursAgo,
            ];
        }

        return ['status' => false, 'message' => 'No hay backups registrados'];
    }
}
