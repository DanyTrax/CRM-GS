<?php

namespace App\Services;

use App\Models\Backup;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use ZipArchive;

class BackupService
{
    /**
     * Crea un backup completo del sistema
     */
    public function createBackup(): Backup
    {
        $filename = 'backup_' . now()->format('Y-m-d_His') . '.zip';
        $path = storage_path('app/backups/' . $filename);

        // Crear directorio si no existe
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $backup = Backup::create([
            'filename' => $filename,
            'path' => $path,
            'size' => 0,
            'status' => 'pending',
            'storage_type' => 'local',
        ]);

        try {
            $zip = new ZipArchive();
            if ($zip->open($path, ZipArchive::CREATE) !== TRUE) {
                throw new \Exception('No se pudo crear el archivo ZIP');
            }

            // Backup de base de datos
            $this->backupDatabase($zip);

            // Backup de archivos
            $this->backupFiles($zip);

            $zip->close();

            $backup->update([
                'size' => filesize($path),
                'status' => 'completed',
            ]);

            // Subir a nube si está configurado
            $this->uploadToCloud($backup);

            // Limpiar backups antiguos
            $this->cleanOldBackups();

        } catch (\Exception $e) {
            $backup->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }

        return $backup;
    }

    /**
     * Backup de base de datos
     */
    protected function backupDatabase(ZipArchive $zip): void
    {
        $dbName = config('database.connections.mysql.database');
        $dbUser = config('database.connections.mysql.username');
        $dbPass = config('database.connections.mysql.password');
        $dbHost = config('database.connections.mysql.host');

        $sqlFile = storage_path('app/temp/database.sql');
        
        if (!is_dir(dirname($sqlFile))) {
            mkdir(dirname($sqlFile), 0755, true);
        }

        $command = sprintf(
            'mysqldump -h %s -u %s -p%s %s > %s',
            escapeshellarg($dbHost),
            escapeshellarg($dbUser),
            escapeshellarg($dbPass),
            escapeshellarg($dbName),
            escapeshellarg($sqlFile)
        );

        exec($command);

        if (file_exists($sqlFile)) {
            $zip->addFile($sqlFile, 'database.sql');
        }
    }

    /**
     * Backup de archivos importantes
     */
    protected function backupFiles(ZipArchive $zip): void
    {
        $files = [
            '.env',
            'storage/app/public',
        ];

        foreach ($files as $file) {
            $fullPath = base_path($file);
            if (file_exists($fullPath)) {
                if (is_dir($fullPath)) {
                    $this->addDirectoryToZip($zip, $fullPath, $file);
                } else {
                    $zip->addFile($fullPath, $file);
                }
            }
        }
    }

    /**
     * Agrega un directorio al ZIP
     */
    protected function addDirectoryToZip(ZipArchive $zip, string $dir, string $basePath): void
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen(base_path($basePath)) + 1);
                $zip->addFile($filePath, $basePath . '/' . $relativePath);
            }
        }
    }

    /**
     * Sube el backup a la nube
     */
    protected function uploadToCloud(Backup $backup): void
    {
        // Implementar integración con Google Drive o OneDrive
        // Por ahora solo marca como completado
    }

    /**
     * Limpia backups antiguos
     */
    protected function cleanOldBackups(): void
    {
        $retentionDays = config('backup.retention_days', 30);
        $cutoffDate = Carbon::now()->subDays($retentionDays);

        $oldBackups = Backup::where('created_at', '<', $cutoffDate)->get();

        foreach ($oldBackups as $backup) {
            if (file_exists($backup->path)) {
                unlink($backup->path);
            }
            $backup->delete();
        }
    }
}
