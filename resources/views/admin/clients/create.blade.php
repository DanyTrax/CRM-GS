@extends('layouts.app')

@section('title', 'Nuevo Cliente')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8">Nuevo Cliente</h1>

    <div class="bg-white rounded-lg shadow p-6 max-w-2xl">
        <form action="{{ route('admin.clients.store') }}" method="POST">
            @csrf

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Tipo de Entidad</label>
                <select name="entity_type" required class="w-full border rounded px-3 py-2">
                    <option value="natural">Persona Natural</option>
                    <option value="juridical">Persona Jurídica</option>
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Nombre / Razón Social *</label>
                <input type="text" name="name" required class="w-full border rounded px-3 py-2">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Tipo de Documento *</label>
                <input type="text" name="id_type" required placeholder="CC, NIT, etc." class="w-full border rounded px-3 py-2">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Número de Documento *</label>
                <input type="text" name="id_number" required class="w-full border rounded px-3 py-2">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Email *</label>
                <input type="email" name="email" required class="w-full border rounded px-3 py-2">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Email de Facturación</label>
                <input type="email" name="billing_email" class="w-full border rounded px-3 py-2">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Teléfono</label>
                <input type="text" name="phone" class="w-full border rounded px-3 py-2">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Dirección</label>
                <textarea name="address" class="w-full border rounded px-3 py-2"></textarea>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Ciudad</label>
                <input type="text" name="city" class="w-full border rounded px-3 py-2">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Contraseña (opcional - para crear usuario)</label>
                <input type="password" name="password" class="w-full border rounded px-3 py-2">
            </div>

            <div class="mb-4">
                <label class="flex items-center">
                    <input type="checkbox" name="silent_mode" value="1" class="mr-2">
                    <span class="text-gray-700 text-sm">Silenciar notificaciones (modo silencioso)</span>
                </label>
            </div>

            <div class="flex justify-end space-x-4">
                <a href="{{ route('admin.clients.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                    Cancelar
                </a>
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    Guardar Cliente
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
