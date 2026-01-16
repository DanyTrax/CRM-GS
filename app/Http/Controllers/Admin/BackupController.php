<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Backup;
use App\Services\BackupService;
use Illuminate\Http\Request;

class BackupController extends Controller
{
    public function index()
    {
        $backups = Backup::latest()->paginate(15);
        return view('admin.backups.index', compact('backups'));
    }

    public function create(BackupService $backupService)
    {
        $backup = $backupService->createBackup();
        
        return redirect()->route('admin.backups.index')
            ->with('success', 'Backup creado exitosamente');
    }

    public function download(Backup $backup)
    {
        if (!file_exists($backup->path)) {
            return redirect()->back()
                ->with('error', 'El archivo de backup no existe');
        }

        return response()->download($backup->path, $backup->filename);
    }
}
