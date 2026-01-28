@extends('layouts.app')

@section('title', 'Dashboard - Cliente')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8">Mi Dashboard</h1>

    <!-- Estadísticas -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-gray-600 text-sm font-medium mb-2">Servicios Activos</h3>
            <p class="text-3xl font-bold text-green-600">{{ $activeServices }}</p>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-gray-600 text-sm font-medium mb-2">Facturas Pendientes</h3>
            <p class="text-3xl font-bold text-yellow-600">${{ number_format($pendingInvoices, 2) }}</p>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-gray-600 text-sm font-medium mb-2">Tickets Abiertos</h3>
            <p class="text-3xl font-bold text-red-600">{{ $openTickets }}</p>
        </div>
    </div>

    <!-- Accesos Rápidos -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <a href="{{ route('client.services') }}" class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition">
            <h3 class="text-lg font-semibold mb-2">Mis Servicios</h3>
            <p class="text-gray-600 text-sm">Ver todos mis servicios contratados</p>
        </a>

        <a href="{{ route('client.invoices') }}" class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition">
            <h3 class="text-lg font-semibold mb-2">Mis Facturas</h3>
            <p class="text-gray-600 text-sm">Ver historial de facturación</p>
        </a>

        <a href="{{ route('client.tickets') }}" class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition">
            <h3 class="text-lg font-semibold mb-2">Mis Tickets</h3>
            <p class="text-gray-600 text-sm">Solicitudes de soporte</p>
        </a>

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-2">Contacto</h3>
            <p class="text-gray-600 text-sm">Solicitar soporte técnico</p>
        </div>
    </div>
</div>
@endsection
