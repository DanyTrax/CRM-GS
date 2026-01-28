<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\TicketController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\HealthCheckController;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Clientes
Route::resource('clients', ClientController::class)->middleware('permission:clients.view');

// Servicios
Route::resource('services', ServiceController::class)->middleware('permission:services.view');
Route::post('services/{service}/renew', [ServiceController::class, 'renew'])->name('services.renew');

// Facturas
Route::resource('invoices', InvoiceController::class)->middleware('permission:invoices.view');
Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'downloadPdf'])->name('invoices.pdf');

// Pagos
Route::resource('payments', PaymentController::class)->middleware('permission:payments.view');
Route::post('payments/{payment}/approve', [PaymentController::class, 'approve'])->name('payments.approve');
Route::post('payments/{payment}/reject', [PaymentController::class, 'reject'])->name('payments.reject');

// Tickets
Route::resource('tickets', TicketController::class)->middleware('permission:tickets.view');

// Roles y Permisos
Route::resource('roles', RoleController::class)->middleware('permission:roles.view');

// Backups
Route::get('backups', [BackupController::class, 'index'])->name('backups.index')->middleware('permission:settings.backup');
Route::post('backups/create', [BackupController::class, 'create'])->name('backups.create');
Route::get('backups/{backup}/download', [BackupController::class, 'download'])->name('backups.download');

// Health Check
Route::get('health', [HealthCheckController::class, 'index'])->name('health.index');
