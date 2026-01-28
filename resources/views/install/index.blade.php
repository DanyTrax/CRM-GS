<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalación - CRM Gestor de Servicios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .install-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            max-width: 800px;
            width: 100%;
            padding: 40px;
        }
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
        }
        .step {
            flex: 1;
            text-align: center;
            padding: 10px;
            position: relative;
        }
        .step::after {
            content: '';
            position: absolute;
            top: 20px;
            left: 50%;
            width: 100%;
            height: 2px;
            background: #dee2e6;
            z-index: -1;
        }
        .step:last-child::after {
            display: none;
        }
        .step.active .step-number {
            background: #667eea;
            color: white;
        }
        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #dee2e6;
            color: #6c757d;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="install-container">
        <h2 class="text-center mb-4">Instalación del Sistema</h2>
        
        <div class="step-indicator">
            <div class="step active" id="step1-indicator">
                <div class="step-number">1</div>
                <div>Base de Datos</div>
            </div>
            <div class="step" id="step2-indicator">
                <div class="step-number">2</div>
                <div>Configuración</div>
            </div>
            <div class="step" id="step3-indicator">
                <div class="step-number">3</div>
                <div>Finalizar</div>
            </div>
        </div>

        <!-- Paso 1: Base de Datos -->
        <div id="step1" class="step-content">
            <h4 class="mb-3">Paso 1: Configuración de Base de Datos</h4>
            <form id="step1-form">
                <div class="mb-3">
                    <label class="form-label">Host</label>
                    <input type="text" name="db_host" class="form-control" value="127.0.0.1" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Puerto</label>
                    <input type="number" name="db_port" class="form-control" value="3306" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Base de Datos</label>
                    <input type="text" name="db_database" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Usuario</label>
                    <input type="text" name="db_username" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Contraseña</label>
                    <input type="password" name="db_password" class="form-control">
                </div>
                <button type="submit" class="btn btn-primary">Probar Conexión y Continuar</button>
            </form>
        </div>

        <!-- Paso 2: Configuración -->
        <div id="step2" class="step-content" style="display: none;">
            <h4 class="mb-3">Paso 2: Configuración General</h4>
            <form id="step2-form">
                <div class="mb-3">
                    <label class="form-label">Nombre de la Aplicación</label>
                    <input type="text" name="app_name" class="form-control" value="CRM Gestor de Servicios" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">URL de la Aplicación</label>
                    <input type="url" name="app_url" class="form-control" value="{{ url('/') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email del Administrador</label>
                    <input type="email" name="admin_email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Contraseña del Administrador</label>
                    <input type="password" name="admin_password" class="form-control" minlength="8" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">TRM Base (USD a COP)</label>
                    <input type="number" name="trm_base" class="form-control" value="4000" step="0.01">
                </div>
                <div class="mb-3">
                    <label class="form-label">Spread Bold (%)</label>
                    <input type="number" name="bold_spread" class="form-control" value="3" step="0.1">
                </div>
                <button type="submit" class="btn btn-primary">Guardar y Continuar</button>
            </form>
        </div>

        <!-- Paso 3: Finalizar -->
        <div id="step3" class="step-content" style="display: none;">
            <h4 class="mb-3">Paso 3: Finalizar Instalación</h4>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> 
                Se ejecutarán automáticamente:
                <ul class="mb-0 mt-2">
                    <li>Instalación de dependencias (si es necesario)</li>
                    <li>Generación de APP_KEY</li>
                    <li>Migraciones de base de datos</li>
                    <li>Seeders (roles y configuraciones)</li>
                    <li>Creación de usuario administrador</li>
                </ul>
            </div>
            <p class="text-muted">Este proceso puede tardar unos minutos. Por favor, no cierres esta ventana.</p>
            <button type="button" id="complete-install" class="btn btn-success btn-lg">
                <span id="install-spinner" style="display: none;" class="spinner-border spinner-border-sm me-2"></span>
                Completar Instalación
            </button>
        </div>

        <div id="messages" class="mt-3"></div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentStep = 1;

        function showStep(step) {
            document.querySelectorAll('.step-content').forEach(el => el.style.display = 'none');
            document.getElementById(`step${step}`).style.display = 'block';
            
            document.querySelectorAll('.step').forEach((el, idx) => {
                el.classList.toggle('active', idx + 1 === step);
            });
        }

        function showMessage(message, type = 'success') {
            const messagesDiv = document.getElementById('messages');
            const alert = document.createElement('div');
            alert.className = `alert alert-${type} alert-dismissible fade show`;
            alert.innerHTML = `${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
            messagesDiv.innerHTML = '';
            messagesDiv.appendChild(alert);
        }

        document.getElementById('step1-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            
            try {
                const response = await fetch('{{ route("install.step1") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showMessage(data.message, 'success');
                    setTimeout(() => {
                        currentStep = 2;
                        showStep(2);
                    }, 1000);
                } else {
                    showMessage(data.message, 'danger');
                }
            } catch (error) {
                showMessage('Error: ' + error.message, 'danger');
            }
        });

        document.getElementById('step2-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            
            try {
                const response = await fetch('{{ route("install.step2") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showMessage(data.message, 'success');
                    setTimeout(() => {
                        currentStep = 3;
                        showStep(3);
                    }, 1000);
                } else {
                    showMessage(data.message, 'danger');
                }
            } catch (error) {
                showMessage('Error: ' + error.message, 'danger');
            }
        });

        document.getElementById('complete-install').addEventListener('click', async () => {
            const formData = new FormData(document.getElementById('step2-form'));
            const button = document.getElementById('complete-install');
            const spinner = document.getElementById('install-spinner');
            
            // Deshabilitar botón y mostrar spinner
            button.disabled = true;
            spinner.style.display = 'inline-block';
            
            try {
                showMessage('Instalando... Esto puede tardar varios minutos. Por favor, espera...', 'info');
                
                const response = await fetch('{{ route("install.complete") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showMessage('¡Instalación completada exitosamente! Redirigiendo al login...', 'success');
                    setTimeout(() => {
                        window.location.href = data.redirect || '/login';
                    }, 2000);
                } else {
                    showMessage('Error: ' + data.message + (data.trace ? '<br><small>' + data.trace + '</small>' : ''), 'danger');
                    button.disabled = false;
                    spinner.style.display = 'none';
                }
            } catch (error) {
                showMessage('Error de conexión: ' + error.message, 'danger');
                button.disabled = false;
                spinner.style.display = 'none';
            }
        });
    </script>
</body>
</html>
