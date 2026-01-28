@extends('layouts.install')

@section('content')
<h4 class="mb-4">Paso 2: Configuración de Base de Datos</h4>

<p class="text-muted mb-4">Ingresa los datos de conexión a tu base de datos MySQL. El sistema creará las tablas automáticamente.</p>

<form id="database-form">
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Host de Base de Datos</label>
            <input type="text" name="db_host" class="form-control" value="localhost" required>
            <small class="text-muted">Generalmente: localhost o 127.0.0.1</small>
        </div>
        
        <div class="col-md-6 mb-3">
            <label class="form-label">Puerto</label>
            <input type="number" name="db_port" class="form-control" value="3306" required>
        </div>
    </div>
    
    <div class="mb-3">
        <label class="form-label">Nombre de la Base de Datos</label>
        <input type="text" name="db_database" class="form-control" required>
        <small class="text-muted">La base de datos debe estar creada previamente en tu servidor MySQL</small>
    </div>
    
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Usuario</label>
            <input type="text" name="db_username" class="form-control" required>
        </div>
        
        <div class="col-md-6 mb-3">
            <label class="form-label">Contraseña</label>
            <input type="password" name="db_password" class="form-control">
        </div>
    </div>
    
    <div id="connection-message" class="mt-3"></div>
    
    <div class="d-flex justify-content-between mt-4">
        <a href="{{ route('installer.requirements') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Anterior
        </a>
        <button type="button" class="btn btn-primary" onclick="testConnection()">
            <span id="test-spinner" style="display: none;" class="spinner-border spinner-border-sm me-2"></span>
            Probar Conexión
        </button>
        <button type="button" class="btn btn-success" onclick="saveDatabase()" id="save-btn" style="display: none;">
            Guardar y Continuar <i class="bi bi-arrow-right"></i>
        </button>
    </div>
</form>

<script>
    function testConnection() {
        const form = document.getElementById('database-form');
        const formData = new FormData(form);
        const spinner = document.getElementById('test-spinner');
        const messageDiv = document.getElementById('connection-message');
        const saveBtn = document.getElementById('save-btn');
        
        spinner.style.display = 'inline-block';
        messageDiv.innerHTML = '';
        
        fetch('{{ route("installer.test-database") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(r => r.json())
        .then(data => {
            spinner.style.display = 'none';
            
            if (data.success) {
                messageDiv.innerHTML = `
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle"></i> ${data.message}
                    </div>
                `;
                saveBtn.style.display = 'block';
            } else {
                messageDiv.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-x-circle"></i> ${data.message}
                    </div>
                `;
                saveBtn.style.display = 'none';
            }
        })
        .catch(error => {
            spinner.style.display = 'none';
            messageDiv.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i> Error: ${error.message}
                </div>
            `;
        });
    }
    
    function saveDatabase() {
        const form = document.getElementById('database-form');
        const formData = new FormData(form);
        const messageDiv = document.getElementById('connection-message');
        const saveBtn = document.getElementById('save-btn');
        
        // Deshabilitar botón mientras se procesa
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Guardando...';
        messageDiv.innerHTML = '';
        
        fetch('{{ route("installer.save-database") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(async r => {
            // Verificar si la respuesta es JSON
            const contentType = r.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await r.text();
                throw new Error('El servidor devolvió una respuesta no válida. ' + (text.substring(0, 200) || 'Error desconocido'));
            }
            
            if (!r.ok) {
                // Intentar parsear el JSON de error
                const errorData = await r.json().catch(() => null);
                throw new Error(errorData?.message || `Error HTTP ${r.status}`);
            }
            
            return r.json();
        })
        .then(data => {
            if (data.success) {
                messageDiv.innerHTML = `
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle"></i> ${data.message || 'Configuración guardada exitosamente'}
                    </div>
                `;
                // Redirigir después de un breve delay
                setTimeout(() => {
                    window.location.href = '{{ route("installer.admin") }}';
                }, 500);
            } else {
                messageDiv.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-x-circle"></i> ${data.message || 'Error al guardar la configuración'}
                    </div>
                `;
                saveBtn.disabled = false;
                saveBtn.innerHTML = 'Guardar y Continuar <i class="bi bi-arrow-right"></i>';
            }
        })
        .catch(error => {
            console.error('Error guardando base de datos:', error);
            messageDiv.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i> 
                    <strong>Error:</strong> ${error.message || 'Error desconocido al guardar la configuración'}
                    <br><small>Verifica los logs del servidor o contacta al administrador.</small>
                </div>
            `;
            saveBtn.disabled = false;
            saveBtn.innerHTML = 'Guardar y Continuar <i class="bi bi-arrow-right"></i>';
        });
    }
</script>
@endsection
