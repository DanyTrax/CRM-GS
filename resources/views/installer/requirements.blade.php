@extends('layouts.install')

@section('content')
<h4 class="mb-4">Paso 1: Verificación de Requisitos</h4>

<div id="requirements-check" class="mb-4">
    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i> Verificando requisitos del sistema...
    </div>
</div>

<div id="requirements-results" style="display: none;">
    <table class="table table-hover">
        <thead>
            <tr>
                <th>Requisito</th>
                <th>Estado</th>
                <th>Detalles</th>
            </tr>
        </thead>
        <tbody id="requirements-list">
            <!-- Se llena dinámicamente -->
        </tbody>
    </table>
</div>

<div class="d-flex justify-content-end gap-2">
    <button type="button" class="btn btn-secondary" onclick="checkRequirements()" id="recheck-btn">
        <i class="bi bi-arrow-clockwise"></i> Verificar Nuevamente
    </button>
    <button type="button" class="btn btn-primary" onclick="nextStep()" id="next-btn" style="display: none;">
        Continuar <i class="bi bi-arrow-right"></i>
    </button>
</div>

<script>
    // Datos del servidor (pasados desde PHP)
    const serverData = {
        phpVersion: '{{ PHP_VERSION }}',
        pdoMysql: {{ extension_loaded('pdo_mysql') ? 'true' : 'false' }},
        mbstring: {{ extension_loaded('mbstring') ? 'true' : 'false' }},
        openssl: {{ extension_loaded('openssl') ? 'true' : 'false' }},
        storageWritable: {{ is_writable(storage_path()) ? 'true' : 'false' }},
        composerCheckUrl: '{{ route("installer.check-composer") }}'
    };
    
    function checkRequirements() {
        const results = {
            php_version: { status: 'checking', min: '8.2', current: serverData.phpVersion },
            composer: { status: 'checking' },
            pdo_mysql: { status: 'checking' },
            mbstring: { status: 'checking' },
            openssl: { status: 'checking' },
            storage_writable: { status: 'checking' },
        };
        
        // Verificar PHP
        const phpVersion = parseFloat(serverData.phpVersion);
        if (phpVersion >= 8.2) {
            results.php_version.status = 'success';
        } else {
            results.php_version.status = 'error';
        }
        
        // Verificar extensiones
        results.pdo_mysql.status = serverData.pdoMysql ? 'success' : 'error';
        results.mbstring.status = serverData.mbstring ? 'success' : 'error';
        results.openssl.status = serverData.openssl ? 'success' : 'error';
        
        // Verificar storage
        results.storage_writable.status = serverData.storageWritable ? 'success' : 'error';
        
        // Verificar Composer (requiere llamada AJAX)
        fetch(serverData.composerCheckUrl, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            },
            credentials: 'same-origin'
        })
            .then(r => {
                // Si la respuesta no es OK, intentar parsear el JSON de error
                if (!r.ok) {
                    return r.json().then(err => {
                        throw new Error(err.message || 'Error en la petición');
                    }).catch(() => {
                        throw new Error('Error HTTP ' + r.status);
                    });
                }
                return r.json();
            })
            .then(data => {
                // Si vendor existe, considerar Composer como disponible
                if (data.installed || data.vendor_exists) {
                    results.composer.status = 'success';
                } else {
                    results.composer.status = 'warning';
                }
                renderResults(results);
            })
            .catch((error) => {
                console.error('Error verificando Composer:', error);
                // Si hay error pero vendor/autoload.php existe, considerar como warning
                // porque las dependencias ya están instaladas
                results.composer.status = 'warning';
                renderResults(results);
            });
    }
    
    function renderResults(results) {
        const tbody = document.getElementById('requirements-list');
        tbody.innerHTML = '';
        
        const checks = {
            php_version: { name: 'PHP >= 8.2', icon: 'bi-code-square' },
            composer: { name: 'Composer', icon: 'bi-box' },
            pdo_mysql: { name: 'PDO MySQL', icon: 'bi-database' },
            mbstring: { name: 'Mbstring', icon: 'bi-textarea' },
            openssl: { name: 'OpenSSL', icon: 'bi-shield-lock' },
            storage_writable: { name: 'Storage Escribible', icon: 'bi-folder-check' },
        };
        
        let allPassed = true;
        
        Object.keys(checks).forEach(key => {
            const check = checks[key];
            const result = results[key];
            const row = document.createElement('tr');
            
            let statusIcon = '';
            let statusText = '';
            let statusClass = '';
            
            if (result.status === 'success') {
                statusIcon = '<i class="bi bi-check-circle text-success"></i>';
                statusText = 'OK';
                statusClass = 'text-success';
            } else if (result.status === 'error') {
                statusIcon = '<i class="bi bi-x-circle text-danger"></i>';
                statusText = 'ERROR';
                statusClass = 'text-danger';
                allPassed = false;
            } else {
                statusIcon = '<i class="bi bi-exclamation-triangle text-warning"></i>';
                statusText = 'WARNING';
                statusClass = 'text-warning';
                allPassed = false;
            }
            
            row.innerHTML = `
                <td><i class="bi ${check.icon} me-2"></i>${check.name}</td>
                <td class="${statusClass}">${statusIcon} ${statusText}</td>
                <td>${result.current || result.min || '-'}</td>
            `;
            tbody.appendChild(row);
        });
        
        document.getElementById('requirements-check').style.display = 'none';
        document.getElementById('requirements-results').style.display = 'block';
        
        if (allPassed) {
            document.getElementById('next-btn').style.display = 'block';
        }
    }
    
    function nextStep() {
        window.location.href = '{{ route("installer.database") }}';
    }
    
    // Auto-verificar al cargar
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', checkRequirements);
    } else {
        // DOM ya está listo
        checkRequirements();
    }
</script>
@endsection
