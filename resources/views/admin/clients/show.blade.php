@extends('layouts.app')

@section('title', 'Ver Cliente')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Cliente: {{ $client->name }}</h1>
        <div class="space-x-2">
            <a href="{{ route('admin.clients.edit', $client) }}" class="bg-indigo-500 text-white px-4 py-2 rounded hover:bg-indigo-600">
                Editar
            </a>
            <a href="{{ route('admin.clients.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                Volver
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Información Principal -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4">Información del Cliente</h2>
                <dl class="grid grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Tipo de Entidad</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ ucfirst($client->entity_type) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Documento</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $client->id_type }}: {{ $client->id_number }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Email</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $client->email }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Email Facturación</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $client->billing_email ?: $client->email }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Teléfono</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $client->phone ?: 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Estado</dt>
                        <dd class="mt-1">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                @if($client->status === 'active') bg-green-100 text-green-800
                                @elseif($client->status === 'suspended') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ ucfirst($client->status) }}
                            </span>
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- Servicios -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4">Servicios ({{ $client->services->count() }})</h2>
                @if($client->services->count() > 0)
                    <div class="space-y-2">
                        @foreach($client->services as $service)
                        <div class="border rounded p-3">
                            <h3 class="font-medium">{{ $service->name }}</h3>
                            <p class="text-sm text-gray-600">Estado: {{ ucfirst($service->status) }}</p>
                        </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500">No tiene servicios</p>
                @endif
            </div>

            <!-- Facturas -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4">Facturas ({{ $client->invoices->count() }})</h2>
                @if($client->invoices->count() > 0)
                    <div class="space-y-2">
                        @foreach($client->invoices->take(5) as $invoice)
                        <div class="border rounded p-3 flex justify-between">
                            <div>
                                <h3 class="font-medium">{{ $invoice->invoice_number }}</h3>
                                <p class="text-sm text-gray-600">${{ number_format($invoice->total, 2) }}</p>
                            </div>
                            <span class="px-2 text-xs rounded
                                @if($invoice->status === 'paid') bg-green-100 text-green-800
                                @elseif($invoice->status === 'pending') bg-yellow-100 text-yellow-800
                                @else bg-red-100 text-red-800
                                @endif">
                                {{ ucfirst($invoice->status) }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500">No tiene facturas</p>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div>
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Logs de Correo</h2>
                @if($client->emailLogs->count() > 0)
                    <div class="space-y-2">
                        @foreach($client->emailLogs->take(5) as $log)
                        <div class="border rounded p-2 text-sm">
                            <p class="font-medium">{{ $log->subject }}</p>
                            <p class="text-xs text-gray-500">{{ $log->created_at->format('d/m/Y H:i') }}</p>
                            <span class="text-xs px-1 rounded
                                @if($log->status === 'sent') bg-green-100 text-green-800
                                @elseif($log->status === 'failed') bg-red-100 text-red-800
                                @else bg-yellow-100 text-yellow-800
                                @endif">
                                {{ ucfirst($log->status) }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-sm">No hay logs de correo</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
