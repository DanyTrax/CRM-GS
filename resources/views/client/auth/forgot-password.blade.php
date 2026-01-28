@extends('layouts.app')

@section('title', 'Recuperar Contraseña')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Recuperar Contraseña
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Ingresa tu correo electrónico para recibir un enlace de recuperación
            </p>
        </div>
        <form class="mt-8 space-y-6 bg-white p-8 rounded-lg shadow" action="{{ route('client.forgot-password') }}" method="POST">
            @csrf

            @if (session('status'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">
                    Correo Electrónico
                </label>
                <input 
                    id="email" 
                    name="email" 
                    type="email" 
                    autocomplete="email" 
                    required 
                    value="{{ old('email') }}"
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    placeholder="tu@email.com"
                >
            </div>

            <div class="flex items-center justify-between">
                <a href="{{ route('client.login') }}" class="text-sm font-medium text-blue-600 hover:text-blue-500">
                    ← Volver al inicio de sesión
                </a>
            </div>

            <div>
                <button 
                    type="submit" 
                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                    Enviar Enlace de Recuperación
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
