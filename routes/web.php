<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BoldWebhookController;
use App\Http\Controllers\EmailInterceptorController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\InstallController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Wizard de Instalación (4 pasos)
Route::prefix('install')->name('installer.')->group(function () {
    Route::get('/requirements', [InstallController::class, 'requirements'])->name('requirements');
    Route::get('/check-composer', [InstallController::class, 'checkComposer'])->name('check-composer');
    Route::get('/database', [InstallController::class, 'database'])->name('database');
    Route::post('/test-database', [InstallController::class, 'testDatabase'])->name('test-database');
    Route::post('/save-database', [InstallController::class, 'saveDatabase'])->name('save-database');
    Route::get('/admin', [InstallController::class, 'admin'])->name('admin');
    Route::post('/save-admin', [InstallController::class, 'saveAdmin'])->name('save-admin');
    Route::get('/finish', [InstallController::class, 'finish'])->name('finish');
    Route::post('/complete', [InstallController::class, 'complete'])->name('complete');
});

// Redirigir /install a requirements
Route::get('/install', function () {
    return redirect()->route('installer.requirements');
});

// Rutas públicas
Route::get('/', function () {
    // Si no hay .env o no está instalado, redirigir a instalación
    $isInstalled = file_exists(base_path('.env')) && 
                   file_exists(storage_path('app/.installed')) &&
                   file_exists(base_path('vendor/autoload.php'));
    
    if (!$isInstalled) {
        return redirect()->route('installer.requirements');
    }
    
    // Si está autenticado, redirigir al dashboard
    if (auth()->check()) {
        return redirect('/admin');
    }
    
    // Si no está autenticado, mostrar login de Filament
    return redirect('/admin/login');
});

// Autenticación: Filament maneja el login en /admin/login
// Redirigir /login a /admin/login para mantener compatibilidad
Route::get('/login', function () {
    return redirect()->route('filament.admin.auth.login');
})->name('login');

Route::post('/login', function () {
    // Filament maneja el login, redirigir al panel de admin
    return redirect()->route('filament.admin.auth.login');
});

// Logout: Filament maneja el logout en /admin/logout
// Redirigir /logout a /admin/logout para mantener compatibilidad
Route::get('/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('filament.admin.auth.login');
})->name('logout');

Route::post('/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('filament.admin.auth.login');
});

// Rutas protegidas - Admin
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Clientes
    Route::resource('clients', ClientController::class)->names('clients');
    
    // Servicios
    Route::resource('services', ServiceController::class)->names('services');
    Route::post('/services/{service}/renew', [ServiceController::class, 'renew'])->name('services.renew');
    Route::post('/services/{service}/change-cycle', [ServiceController::class, 'changeCycle'])->name('services.change-cycle');
    
    // Facturas
    Route::resource('invoices', InvoiceController::class)->names('invoices');
    Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('invoices.pdf');
    Route::post('/invoices/{invoice}/mark-paid', [InvoiceController::class, 'markPaid'])->name('invoices.mark-paid');
    
    // Pagos
    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/', function () { return view('admin.payments.index'); })->name('index');
        Route::get('/create', function () { return view('admin.payments.create'); })->name('create');
        Route::post('/', function () { /* Implementar */ })->name('store');
        Route::post('/{payment}/approve', function () { /* Implementar */ })->name('approve');
        Route::post('/{payment}/reject', function () { /* Implementar */ })->name('reject');
    });
    
    // Tickets
    Route::prefix('tickets')->name('tickets.')->group(function () {
        Route::get('/', function () { return view('admin.tickets.index'); })->name('index');
        Route::get('/create', function () { return view('admin.tickets.create'); })->name('create');
        Route::post('/', function () { /* Implementar */ })->name('store');
        Route::get('/{ticket}', function () { return view('admin.tickets.show'); })->name('show');
        Route::post('/{ticket}/messages', function () { /* Implementar */ })->name('messages.store');
        Route::post('/{ticket}/close', function () { /* Implementar */ })->name('close');
    });
    
    // Correos (Interceptor)
    Route::prefix('emails')->name('emails.')->group(function () {
        Route::get('/', function () { return view('admin.emails.index'); })->name('index');
        Route::post('/intercept', [EmailInterceptorController::class, 'intercept'])->name('intercept');
        Route::get('/preview/{emailLog}', [EmailInterceptorController::class, 'preview'])->name('preview');
        Route::put('/{emailLog}/update-content', [EmailInterceptorController::class, 'updateContent'])->name('update-content');
        Route::post('/{emailLog}/send', [EmailInterceptorController::class, 'send'])->name('send');
    });
    
    // Configuración (Solo Super Admin)
    Route::middleware(['role:super-admin'])->prefix('settings')->name('settings.')->group(function () {
        Route::get('/', function () { return view('admin.settings.index'); })->name('index');
        Route::put('/', function () { /* Implementar */ })->name('update');
    });
});

// API Routes - Webhooks (sin autenticación web)
Route::prefix('api')->group(function () {
    Route::post('/bold/webhook', [BoldWebhookController::class, 'handle'])->name('api.bold.webhook');
});

// Área de Cliente
Route::prefix('client')->name('client.')->middleware(['auth', 'role:cliente'])->group(function () {
    Route::get('/dashboard', function () { return view('client.dashboard'); })->name('dashboard');
    Route::get('/invoices', function () { return view('client.invoices'); })->name('invoices');
    Route::get('/payments', function () { return view('client.payments'); })->name('payments');
    Route::get('/tickets', function () { return view('client.tickets'); })->name('tickets');
    Route::get('/downloads', function () { return view('client.downloads'); })->name('downloads');
});
