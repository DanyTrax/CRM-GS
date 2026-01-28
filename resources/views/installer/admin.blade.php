@extends('layouts.install')

@section('content')
<h4 class="mb-4">Paso 3: Crear Usuario Administrador</h4>

<p class="text-muted mb-4">Crea el primer usuario administrador del sistema. Podrás crear más usuarios después.</p>

<form id="admin-form">
    <div class="mb-3">
        <label class="form-label">Nombre Completo</label>
        <input type="text" name="name" class="form-control" required>
    </div>
    
    <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" required>
        <small class="text-muted">Usa este email para iniciar sesión</small>
    </div>
    
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Contraseña</label>
            <input type="password" name="password" class="form-control" minlength="8" required>
        </div>
        
        <div class="col-md-6 mb-3">
            <label class="form-label">Confirmar Contraseña</label>
            <input type="password" name="password_confirmation" class="form-control" minlength="8" required>
        </div>
    </div>
    
    <div id="admin-message" class="mt-3"></div>
    
    <div class="d-flex justify-content-between mt-4">
        <a href="{{ route('installer.database') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Anterior
        </a>
        <button type="button" class="btn btn-success" onclick="saveAdmin()">
            <span id="save-spinner" style="display: none;" class="spinner-border spinner-border-sm me-2"></span>
            Guardar y Continuar <i class="bi bi-arrow-right"></i>
        </button>
    </div>
</form>

<script>
    function saveAdmin() {
        const form = document.getElementById('admin-form');
        const formData = new FormData(form);
        const spinner = document.getElementById('save-spinner');
        const messageDiv = document.getElementById('admin-message');
        
        // Validar contraseñas
        const password = form.querySelector('[name="password"]').value;
        const passwordConfirmation = form.querySelector('[name="password_confirmation"]').value;
        
        if (password !== passwordConfirmation) {
            messageDiv.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-x-circle"></i> Las contraseñas no coinciden
                </div>
            `;
            return;
        }
        
        if (password.length < 8) {
            messageDiv.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-x-circle"></i> La contraseña debe tener al menos 8 caracteres
                </div>
            `;
            return;
        }
        
        spinner.style.display = 'inline-block';
        messageDiv.innerHTML = '';
        
        const saveBtn = document.querySelector('button[onclick="saveAdmin()"]');
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Guardando...';
        
        fetch('{{ route("installer.save-admin") }}', {
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
            spinner.style.display = 'none';
            
            if (data.success) {
                messageDiv.innerHTML = `
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle"></i> ${data.message || 'Usuario guardado correctamente'}
                    </div>
                `;
                // Redirigir después de un breve delay
                setTimeout(() => {
                    window.location.href = '{{ route("installer.finish") }}';
                }, 500);
            } else {
                messageDiv.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-x-circle"></i> ${data.message || 'Error al guardar el usuario'}
                        ${data.errors ? '<br><small>' + Object.values(data.errors).flat().join(', ') + '</small>' : ''}
                    </div>
                `;
                saveBtn.disabled = false;
                saveBtn.innerHTML = 'Guardar y Continuar <i class="bi bi-arrow-right"></i>';
            }
        })
        .catch(error => {
            console.error('Error guardando usuario:', error);
            spinner.style.display = 'none';
            messageDiv.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i> 
                    <strong>Error:</strong> ${error.message || 'Error desconocido al guardar el usuario'}
                    <br><small>Verifica los logs del servidor o contacta al administrador.</small>
                </div>
            `;
            saveBtn.disabled = false;
            saveBtn.innerHTML = 'Guardar y Continuar <i class="bi bi-arrow-right"></i>';
        });
    }
</script>
@endsection
