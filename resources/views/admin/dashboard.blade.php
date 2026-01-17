@extends('layouts.app')

@section('title', 'Dashboard - Admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8">Dashboard Administrativo</h1>

    <!-- Estadísticas -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-gray-600 text-sm font-medium mb-2">Total Clientes</h3>
            <p class="text-3xl font-bold text-gray-900">{{ $stats['total_clients'] }}</p>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-gray-600 text-sm font-medium mb-2">Servicios Activos</h3>
            <p class="text-3xl font-bold text-green-600">{{ $stats['active_services'] }}</p>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-gray-600 text-sm font-medium mb-2">Facturas Pendientes</h3>
            <p class="text-3xl font-bold text-yellow-600">{{ $stats['pending_invoices'] }}</p>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-gray-600 text-sm font-medium mb-2">Ingresos Totales</h3>
            <p class="text-3xl font-bold text-green-600">${{ number_format($stats['total_revenue'], 2) }}</p>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-gray-600 text-sm font-medium mb-2">Tickets Abiertos</h3>
            <p class="text-3xl font-bold text-red-600">{{ $stats['open_tickets'] }}</p>
        </div>
    </div>

    <!-- Facturas Recientes -->
    <div class="bg-white rounded-lg shadow mb-8">
        <div class="p-6 border-b">
            <h2 class="text-xl font-semibold">Facturas Recientes</h2>
        </div>
        <div class="p-6">
            @if($recentInvoices->count() > 0)
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Número</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($recentInvoices as $invoice)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $invoice->invoice_number }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $invoice->client->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${{ number_format($invoice->total, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                    @if($invoice->status === 'paid') bg-green-100 text-green-800
                                    @elseif($invoice->status === 'pending') bg-yellow-100 text-yellow-800
                                    @else bg-red-100 text-red-800
                                    @endif">
                                    {{ ucfirst($invoice->status) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-gray-500">No hay facturas recientes</p>
            @endif
        </div>
    </div>

    <!-- Pagos Recientes -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
            <h2 class="text-xl font-semibold">Pagos Recientes</h2>
        </div>
        <div class="p-6">
            @if($recentPayments->count() > 0)
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Monto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Método</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($recentPayments as $payment)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#{{ $payment->id }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${{ number_format($payment->amount, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ ucfirst($payment->method) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                    @if($payment->status === 'approved') bg-green-100 text-green-800
                                    @elseif($payment->status === 'pending') bg-yellow-100 text-yellow-800
                                    @else bg-red-100 text-red-800
                                    @endif">
                                    {{ ucfirst($payment->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $payment->payment_date->format('d/m/Y') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-gray-500">No hay pagos recientes</p>
            @endif
        </div>
    </div>
</div>
@endsection
