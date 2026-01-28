@extends('layouts.app')

@section('title', 'Dashboard - Admin')
@section('page-title', 'Dashboard')

@section('content')
<!-- Estadísticas Rápidas -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2 text-white-50">Clientes</h6>
                        <h3 class="mb-0">{{ number_format($stats['total_clients']) }}</h3>
                    </div>
                    <i class="bi bi-people fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2 text-white-50">Servicios Activos</h6>
                        <h3 class="mb-0">{{ number_format($stats['active_services']) }}</h3>
                    </div>
                    <i class="bi bi-box-seam fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2 text-white-50">Facturas Pendientes</h6>
                        <h3 class="mb-0">{{ number_format($stats['pending_invoices']) }}</h3>
                    </div>
                    <i class="bi bi-receipt fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card text-white bg-danger">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2 text-white-50">Tickets Abiertos</h6>
                        <h3 class="mb-0">{{ number_format($stats['open_tickets']) }}</h3>
                    </div>
                    <i class="bi bi-ticket-perforated fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Panel de Salud (Health Check) -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="bi bi-heart-pulse"></i> Panel de Salud del Sistema
        </h5>
        <span class="badge badge-health {{ $healthStatus['overall'] ? 'status-healthy' : 'status-danger' }}">
            {{ $healthStatus['overall'] ? 'Todo OK' : 'Problemas Detectados' }}
        </span>
    </div>
    <div class="card-body">
        <div class="row">
            <!-- Estado de Base de Datos -->
            <div class="col-md-6 mb-3">
                <div class="d-flex justify-content-between align-items-center p-3 border rounded">
                    <div>
                        <h6 class="mb-1">
                            <i class="bi bi-database"></i> Base de Datos
                        </h6>
                        <small class="text-muted">{{ $healthStatus['database']['message'] }}</small>
                    </div>
                    <span class="badge {{ $healthStatus['database']['status'] ? 'bg-success' : 'bg-danger' }}">
                        {{ $healthStatus['database']['status'] ? 'OK' : 'ERROR' }}
                    </span>
                </div>
            </div>
            
            <!-- Estado de Cola (Queue) -->
            <div class="col-md-6 mb-3">
                <div class="d-flex justify-content-between align-items-center p-3 border rounded">
                    <div>
                        <h6 class="mb-1">
                            <i class="bi bi-queue"></i> Cola de Trabajos
                        </h6>
                        <small class="text-muted">{{ $healthStatus['queue']['message'] }}</small>
                        @if(isset($healthStatus['queue']['jobs_failed']) && $healthStatus['queue']['jobs_failed'] > 0)
                        <div>
                            <small class="text-danger">
                                <i class="bi bi-exclamation-triangle"></i> 
                                {{ $healthStatus['queue']['jobs_failed'] }} trabajos fallidos
                            </small>
                        </div>
                        @endif
                    </div>
                    <span class="badge {{ $healthStatus['queue']['status'] ? 'bg-success' : 'bg-danger' }}">
                        {{ $healthStatus['queue']['status'] ? 'OK' : 'ERROR' }}
                    </span>
                </div>
            </div>
            
            <!-- Estado de Cron -->
            <div class="col-md-6 mb-3">
                <div class="d-flex justify-content-between align-items-center p-3 border rounded">
                    <div>
                        <h6 class="mb-1">
                            <i class="bi bi-clock"></i> Tareas Programadas (Cron)
                        </h6>
                        <small class="text-muted">{{ $healthStatus['cron']['message'] }}</small>
                        @if(isset($healthStatus['cron']['last_run']))
                        <div>
                            <small class="text-info">
                                <i class="bi bi-calendar"></i> 
                                {{ $healthStatus['cron']['last_run'] }}
                            </small>
                        </div>
                        @endif
                    </div>
                    <span class="badge {{ $healthStatus['cron']['status'] ? 'bg-success' : 'bg-warning' }}">
                        {{ $healthStatus['cron']['status'] ? 'OK' : 'WARN' }}
                    </span>
                </div>
            </div>
            
            <!-- Estado de Almacenamiento -->
            <div class="col-md-6 mb-3">
                <div class="d-flex justify-content-between align-items-center p-3 border rounded">
                    <div>
                        <h6 class="mb-1">
                            <i class="bi bi-hdd"></i> Almacenamiento
                        </h6>
                        <small class="text-muted">{{ $healthStatus['storage']['message'] }}</small>
                        @if(isset($healthStatus['storage']['free_space_gb']))
                        <div>
                            <small class="text-info">
                                <i class="bi bi-cloud-download"></i> 
                                Libre: {{ $healthStatus['storage']['free_space_gb'] }} GB
                            </small>
                        </div>
                        @endif
                    </div>
                    <span class="badge {{ $healthStatus['storage']['status'] ? 'bg-success' : 'bg-warning' }}">
                        {{ $healthStatus['storage']['status'] ? 'OK' : 'WARN' }}
                    </span>
                </div>
            </div>
            
            <!-- Último Backup -->
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center p-3 border rounded">
                    <div>
                        <h6 class="mb-1">
                            <i class="bi bi-cloud-upload"></i> Último Backup
                        </h6>
                        <small class="text-muted">{{ $healthStatus['last_backup']['message'] }}</small>
                        @if(isset($healthStatus['last_backup']['hours_ago']))
                        <div>
                            <small class="text-info">
                                <i class="bi bi-clock-history"></i> 
                                Hace {{ $healthStatus['last_backup']['hours_ago'] }} horas
                            </small>
                        </div>
                        @endif
                    </div>
                    <span class="badge {{ $healthStatus['last_backup']['status'] ? 'bg-success' : 'bg-warning' }}">
                        {{ $healthStatus['last_backup']['status'] ? 'OK' : 'WARN' }}
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Acciones Rápidas -->
        <div class="mt-4 pt-3 border-top">
            <h6 class="mb-3">Acciones Rápidas</h6>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-primary" onclick="refreshHealthCheck()">
                    <i class="bi bi-arrow-clockwise"></i> Actualizar Estado
                </button>
                @if(isset($healthStatus['queue']['jobs_failed']) && $healthStatus['queue']['jobs_failed'] > 0)
                <a href="{{ route('admin.queue.failed') }}" class="btn btn-sm btn-danger">
                    <i class="bi bi-exclamation-triangle"></i> Ver Trabajos Fallidos
                </a>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Actividad Reciente (Opcional) -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-clock-history"></i> Facturas Recientes</h6>
            </div>
            <div class="card-body">
                <p class="text-muted">Próximamente: Lista de facturas recientes...</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-bell"></i> Notificaciones</h6>
            </div>
            <div class="card-body">
                <p class="text-muted">Próximamente: Notificaciones y alertas...</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function refreshHealthCheck() {
        window.location.reload();
    }
</script>
@endpush
