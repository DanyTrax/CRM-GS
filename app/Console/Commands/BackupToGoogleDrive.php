<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Setting;
use Carbon\Carbon;

class BackupToGoogleDrive extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:google-drive {--force : Forzar backup aunque no sea hora programada}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hacer backup de la base de datos a Google Drive';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Verificar si está habilitado
        if (!Setting::get('backup_enabled', true)) {
            $this->info('Backups están deshabilitados en configuración.');
            return 0;
        }

        $this->info('Iniciando backup a Google Drive...');

        try {
            // 1. Exportar base de datos
            $dbName = config('database.connections.mysql.database');
            $dbUser = config('database.connections.mysql.username');
            $dbPass = config('database.connections.mysql.password');
            $dbHost = config('database.connections.mysql.host');

            $filename = 'backup_' . $dbName . '_' . now()->format('Y-m-d_His') . '.sql';
            $filepath = storage_path('app/backups/' . $filename);

            // Crear directorio si no existe
            if (!is_dir(dirname($filepath))) {
                mkdir(dirname($filepath), 0755, true);
            }

            // Comando mysqldump
            $command = sprintf(
                'mysqldump -h %s -u %s -p%s %s > %s',
                escapeshellarg($dbHost),
                escapeshellarg($dbUser),
                escapeshellarg($dbPass),
                escapeshellarg($dbName),
                escapeshellarg($filepath)
            );

            exec($command, $output, $returnVar);

            if ($returnVar !== 0) {
                throw new \Exception('Error al exportar la base de datos. Verificar credenciales MySQL.');
            }

            // Comprimir el archivo SQL
            $zipFilename = str_replace('.sql', '.zip', $filename);
            $zipFilepath = storage_path('app/backups/' . $zipFilename);

            $zip = new \ZipArchive();
            if ($zip->open($zipFilepath, \ZipArchive::CREATE) === true) {
                $zip->addFile($filepath, $filename);
                $zip->close();
                unlink($filepath); // Eliminar SQL sin comprimir
            } else {
                throw new \Exception('Error al crear archivo ZIP');
            }

            $this->info("Backup creado localmente: {$zipFilename}");

            // 2. Subir a Google Drive (implementar según API de Google Drive)
            // NOTA: Requiere configuración de Google Drive API
            $this->uploadToGoogleDrive($zipFilepath, $zipFilename);

            // 3. Actualizar fecha de último backup
            Setting::set('last_backup_date', now()->toDateTimeString(), 'string');

            // 4. Limpiar backups antiguos (mantener últimos 7 días)
            $this->cleanOldBackups();

            $this->info('✓ Backup completado exitosamente.');

            return 0;
        } catch (\Exception $e) {
            $this->error('Error en backup: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Subir archivo a Google Drive
     * 
     * NOTA: Requiere implementación con Google Drive API v3
     * Ejemplo básico usando Guzzle HTTP Client
     */
    protected function uploadToGoogleDrive(string $filepath, string $filename)
    {
        $folderId = Setting::get('google_drive_folder_id');

        if (empty($folderId)) {
            $this->warn('Google Drive Folder ID no configurado. Backup guardado solo localmente.');
            return;
        }

        // TODO: Implementar upload usando Google Drive API
        // Requiere:
        // 1. Instalar: composer require google/apiclient
        // 2. Configurar credenciales OAuth2 en .env
        // 3. Implementar lógica de autenticación y upload

        $this->info("Backup guardado localmente. Upload a Google Drive requiere configuración.");
    }

    /**
     * Limpiar backups antiguos (mantener últimos 7 días)
     */
    protected function cleanOldBackups()
    {
        $backupDir = storage_path('app/backups');
        $files = glob($backupDir . '/backup_*.zip');
        $daysToKeep = 7;
        $cutoffDate = now()->subDays($daysToKeep);

        $deletedCount = 0;
        foreach ($files as $file) {
            if (filemtime($file) < $cutoffDate->timestamp) {
                unlink($file);
                $deletedCount++;
            }
        }

        if ($deletedCount > 0) {
            $this->info("Eliminados {$deletedCount} backups antiguos (anteriores a {$daysToKeep} días).");
        }
    }
}
