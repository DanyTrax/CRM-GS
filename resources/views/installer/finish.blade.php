@extends('layouts.install')

@section('content')
<h4 class="mb-4">Paso 4: Finalizar Instalación</h4>

<div class="alert alert-info mb-4">
    <i class="bi bi-info-circle"></i> 
    <strong>Preparando el sistema...</strong><br>
    Se ejecutarán automáticamente las migraciones y la configuración inicial. Esto puede tardar unos minutos.
</div>

<div id="installation-progress" class="mb-4">
    <div class="progress" style="height: 30px;">
        <div class="progress-bar progress-bar-striped progress-bar-animated" 
             role="progressbar" 
             style="width: 0%" 
             id="progress-bar">
            <span id="progress-text">0%</span>
        </div>
    </div>
    <div id="progress-messages" class="mt-3"></div>
</div>

<div id="installation-complete" style="display: none;">
    <div class="alert alert-success">
        <h5><i class="bi bi-check-circle"></i> ¡Instalación Completada!</h5>
        <p class="mb-0">El sistema está listo para usar. Serás redirigido al login en unos segundos...</p>
    </div>
    <a href="{{ route('login') }}" class="btn btn-success btn-lg">
        <i class="bi bi-box-arrow-in-right"></i> Ir al Login
    </a>
</div>

<script>
    let currentProgress = 0;
    
    function updateProgress(percent, message) {
        const progressBar = document.getElementById('progress-bar');
        const progressText = document.getElementById('progress-text');
        const messagesDiv = document.getElementById('progress-messages');
        
        currentProgress = percent;
        progressBar.style.width = percent + '%';
        progressText.textContent = percent + '%';
        
        if (message) {
            messagesDiv.innerHTML += `<div class="small text-muted"><i class="bi bi-check"></i> ${message}</div>`;
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
        }
    }
    
    function completeInstallation() {
        fetch('{{ route("installer.complete") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                updateProgress(100, '¡Instalación completada!');
                
                setTimeout(() => {
                    document.getElementById('installation-progress').style.display = 'none';
                    document.getElementById('installation-complete').style.display = 'block';
                    
                    setTimeout(() => {
                        window.location.href = data.redirect || '{{ route("login") }}';
                    }, 3000);
                }, 1000);
            } else {
                document.getElementById('progress-messages').innerHTML += `
                    <div class="alert alert-danger mt-3">
                        <i class="bi bi-x-circle"></i> Error: ${data.message}
                    </div>
                `;
            }
        })
        .catch(error => {
            document.getElementById('progress-messages').innerHTML += `
                <div class="alert alert-danger mt-3">
                    <i class="bi bi-exclamation-triangle"></i> Error: ${error.message}
                </div>
            `;
        });
    }
    
    // Simular progreso mientras se ejecuta la instalación
    document.addEventListener('DOMContentLoaded', function() {
        updateProgress(10, 'Verificando dependencias...');
        
        setTimeout(() => {
            updateProgress(30, 'Instalando dependencias de Composer...');
        }, 500);
        
        setTimeout(() => {
            updateProgress(50, 'Creando archivo .env...');
        }, 2000);
        
        setTimeout(() => {
            updateProgress(70, 'Ejecutando migraciones de base de datos...');
        }, 4000);
        
        setTimeout(() => {
            updateProgress(90, 'Creando usuario administrador...');
        }, 6000);
        
        setTimeout(() => {
            completeInstallation();
        }, 8000);
    });
</script>
@endsection
