<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Install\InstallController;
use App\Http\Controllers\Client\AuthController as ClientAuthController;
use App\Http\Controllers\Client\DashboardController as ClientDashboardController;
use App\Http\Controllers\Api\BoldWebhookController;

// Wizard de instalación
Route::get('/install', [InstallController::class, 'index'])->name('install.index');
Route::post('/install/requirements', [InstallController::class, 'checkRequirements'])->name('install.requirements');
Route::post('/install/database', [InstallController::class, 'testDatabase'])->name('install.database');
Route::post('/install/admin', [InstallController::class, 'createAdmin'])->name('install.admin');
Route::post('/install/run', [InstallController::class, 'runMigrations'])->name('install.run');

// Webhook de Bold
Route::post('/api/bold/webhook', [BoldWebhookController::class, 'handle'])->name('bold.webhook');

// Ruta de login genérica (para que el middleware auth funcione)
Route::get('/login', function () {
    return redirect()->route('client.login');
})->name('login');

// Rutas públicas del área de cliente
Route::prefix('client')->name('client.')->group(function () {
    Route::get('/login', [ClientAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [ClientAuthController::class, 'login']);
    Route::post('/logout', [ClientAuthController::class, 'logout'])->name('logout');
    Route::get('/forgot-password', [ClientAuthController::class, 'showForgotPassword'])->name('forgot-password');
    Route::post('/forgot-password', [ClientAuthController::class, 'sendResetLink']);
    
    // Rutas protegidas del cliente (solo requieren autenticación, el middleware client es opcional)
    Route::middleware(['auth'])->group(function () {
        Route::get('/dashboard', [ClientDashboardController::class, 'index'])->name('dashboard');
        Route::get('/services', [ClientDashboardController::class, 'services'])->name('services');
        Route::get('/invoices', [ClientDashboardController::class, 'invoices'])->name('invoices');
        Route::get('/tickets', [ClientDashboardController::class, 'tickets'])->name('tickets');
    });
});

// Ruta de login para admin (pública)
Route::get('/admin/login', function () {
    return redirect()->route('client.login');
})->name('admin.login');

// Rutas del panel admin (requieren autenticación y permisos)
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:Super Administrador|Administrador Operativo|Contador|Soporte'])->group(function () {
    require __DIR__.'/admin.php';
});
