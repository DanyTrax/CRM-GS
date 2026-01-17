@extends('layouts.app')

@section('title', 'Instalación - CRM-GS')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold mb-8">Instalación de CRM-GS</h1>
        
        <div id="install-wizard" class="bg-white rounded-lg shadow p-6">
            <!-- Paso 1: Requisitos -->
            <div id="step-1" class="step">
                <h2 class="text-2xl font-semibold mb-4">Paso 1: Verificar Requisitos</h2>
                <div id="requirements-check" class="space-y-2">
                    <p>Verificando requisitos del sistema...</p>
                </div>
                <button onclick="checkRequirements()" class="mt-4 bg-blue-500 text-white px-4 py-2 rounded">
                    Verificar Requisitos
                </button>
            </div>

            <!-- Paso 2: Base de Datos -->
            <div id="step-2" class="step hidden">
                <h2 class="text-2xl font-semibold mb-4">Paso 2: Configurar Base de Datos</h2>
                <form id="database-form" class="space-y-4">
                    <div>
                        <label class="block mb-2">Host</label>
                        <input type="text" name="host" value="127.0.0.1" class="w-full border rounded px-3 py-2" required>
                    </div>
                    <div>
                        <label class="block mb-2">Base de Datos</label>
                        <input type="text" name="database" class="w-full border rounded px-3 py-2" required>
                    </div>
                    <div>
                        <label class="block mb-2">Usuario</label>
                        <input type="text" name="username" class="w-full border rounded px-3 py-2" required>
                    </div>
                    <div>
                        <label class="block mb-2">Contraseña</label>
                        <input type="password" name="password" class="w-full border rounded px-3 py-2">
                    </div>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">
                        Probar Conexión
                    </button>
                </form>
            </div>

            <!-- Paso 3: Instalar Sistema -->
            <div id="step-3" class="step hidden">
                <h2 class="text-2xl font-semibold mb-4">Paso 3: Instalar Sistema</h2>
                <p class="mb-4">Ejecutando migraciones y seeders...</p>
                <div id="install-progress" class="mb-4"></div>
                <button onclick="runInstallation()" class="bg-green-500 text-white px-4 py-2 rounded">
                    Instalar
                </button>
            </div>

            <!-- Paso 4: Administrador -->
            <div id="step-4" class="step hidden">
                <h2 class="text-2xl font-semibold mb-4">Paso 4: Crear Usuario Administrador</h2>
                <form id="admin-form" class="space-y-4">
                    <div>
                        <label class="block mb-2">Nombre</label>
                        <input type="text" name="name" class="w-full border rounded px-3 py-2" required>
                    </div>
                    <div>
                        <label class="block mb-2">Email</label>
                        <input type="email" name="email" class="w-full border rounded px-3 py-2" required>
                    </div>
                    <div>
                        <label class="block mb-2">Contraseña</label>
                        <input type="password" name="password" class="w-full border rounded px-3 py-2" required minlength="8">
                    </div>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">
                        Crear Administrador
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
let currentStep = 1;

// Obtener token CSRF
function getCSRFToken() {
    const token = document.querySelector('meta[name="csrf-token"]');
    return token ? token.getAttribute('content') : '';
}

function checkRequirements() {
    fetch('/install/requirements', { 
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': getCSRFToken(),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        const div = document.getElementById('requirements-check');
        if (data.passed) {
            div.innerHTML = '<p class="text-green-600">✓ Todos los requisitos están cumplidos</p>';
            nextStep();
        } else {
            div.innerHTML = '<p class="text-red-600">✗ Algunos requisitos no se cumplen</p>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al verificar requisitos: ' + error.message);
    });
}

function nextStep() {
    document.getElementById(`step-${currentStep}`).classList.add('hidden');
    currentStep++;
    document.getElementById(`step-${currentStep}`).classList.remove('hidden');
}

document.getElementById('database-form').addEventListener('submit', (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    
    // Agregar token CSRF
    formData.append('_token', getCSRFToken());
    
    fetch('/install/database', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': getCSRFToken(),
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(r => {
        if (!r.ok) {
            return r.json().then(data => Promise.reject(data));
        }
        return r.json();
    })
    .then(data => {
        if (data.success) {
            alert('✓ Conexión a la base de datos exitosa');
            nextStep();
        } else {
            alert('Error: ' + (data.message || 'Error desconocido'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error: ' + (error.message || 'No se pudo conectar a la base de datos'));
    });
});

document.getElementById('admin-form').addEventListener('submit', (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    
    // Agregar token CSRF
    formData.append('_token', getCSRFToken());
    
    fetch('/install/admin', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': getCSRFToken(),
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(r => {
        if (!r.ok) {
            return r.json().then(data => Promise.reject(data));
        }
        return r.json();
    })
    .then(data => {
        if (data.success) {
            alert('✓ Usuario administrador creado exitosamente');
            window.location.href = '/admin';
        } else {
            alert('Error: ' + (data.message || 'Error desconocido'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error: ' + (error.message || 'No se pudo crear el usuario administrador'));
    });
});

function runInstallation() {
    const progressDiv = document.getElementById('install-progress');
    progressDiv.innerHTML = '<p class="text-blue-600">Ejecutando migraciones...</p>';
    
    fetch('/install/run', { 
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': getCSRFToken(),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(r => {
        if (!r.ok) {
            return r.json().then(data => Promise.reject(data));
        }
        return r.json();
    })
    .then(data => {
        if (data.success) {
            progressDiv.innerHTML = '<p class="text-green-600">✓ Instalación completada exitosamente</p>';
            nextStep();
        } else {
            progressDiv.innerHTML = '<p class="text-red-600">✗ Error: ' + (data.message || 'Error desconocido') + '</p>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        progressDiv.innerHTML = '<p class="text-red-600">✗ Error: ' + (error.message || 'Error al ejecutar la instalación') + '</p>';
    });
}
</script>
@endsection
