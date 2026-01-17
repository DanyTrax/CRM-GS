@extends('layouts.app')

@section('title', 'Editar Cliente')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8">Editar Cliente: {{ $client->name }}</h1>

    <div class="bg-white rounded-lg shadow p-6 max-w-2xl">
        <form action="{{ route('admin.clients.update', $client) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Nombre / Razón Social *</label>
                <input type="text" name="name" value="{{ old('name', $client->name) }}" required class="w-full border rounded px-3 py-2">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Email *</label>
                <input type="email" name="email" value="{{ old('email', $client->email) }}" required class="w-full border rounded px-3 py-2">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Email de Facturación</label>
                <input type="email" name="billing_email" value="{{ old('billing_email', $client->billing_email) }}" class="w-full border rounded px-3 py-2">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Documento</label>
                <div class="grid grid-cols-2 gap-4">
                    <input type="text" name="id_type" value="{{ old('id_type', $client->id_type) }}" placeholder="CC, NIT" class="border rounded px-3 py-2">
                    <input type="text" name="id_number" value="{{ old('id_number', $client->id_number) }}" placeholder="Número" class="border rounded px-3 py-2">
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Teléfono</label>
                <input type="text" name="phone" value="{{ old('phone', $client->phone) }}" class="w-full border rounded px-3 py-2">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Dirección</label>
                <textarea name="address" class="w-full border rounded px-3 py-2">{{ old('address', $client->address) }}</textarea>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Ciudad</label>
                <input type="text" name="city" value="{{ old('city', $client->city) }}" class="w-full border rounded px-3 py-2">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Estado</label>
                <select name="status" class="w-full border rounded px-3 py-2">
                    <option value="draft" {{ $client->status === 'draft' ? 'selected' : '' }}>Borrador</option>
                    <option value="active" {{ $client->status === 'active' ? 'selected' : '' }}>Activo</option>
                    <option value="suspended" {{ $client->status === 'suspended' ? 'selected' : '' }}>Suspendido</option>
                </select>
            </div>

            <div class="flex justify-end space-x-4">
                <a href="{{ route('admin.clients.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                    Cancelar
                </a>
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    Actualizar Cliente
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
