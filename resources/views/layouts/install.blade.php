<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Asistente de Instalación - CRM Services</title>
    
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
        }
        
        .install-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            max-width: 900px;
            width: 100%;
            margin: 0 auto;
            padding: 40px;
        }
        
        .install-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .install-header h2 {
            color: #1e293b;
            font-weight: 600;
        }
        
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            position: relative;
        }
        
        .step-indicator::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 0;
            right: 0;
            height: 2px;
            background: #e2e8f0;
            z-index: 0;
        }
        
        .step {
            flex: 1;
            text-align: center;
            padding: 10px;
            position: relative;
            z-index: 1;
        }
        
        .step.active .step-number {
            background: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }
        
        .step.completed .step-number {
            background: #10b981;
            color: white;
            border-color: #10b981;
        }
        
        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: white;
            color: #94a3b8;
            border: 2px solid #e2e8f0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .step-label {
            font-size: 0.875rem;
            color: #64748b;
        }
        
        .step.active .step-label {
            color: #1e293b;
            font-weight: 600;
        }
        
        .step-content {
            min-height: 400px;
        }
        
        .btn-primary {
            background-color: #3b82f6;
            border-color: #3b82f6;
        }
        
        .btn-success {
            background-color: #10b981;
            border-color: #10b981;
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <div class="install-container">
        <div class="install-header">
            <h2><i class="bi bi-gear-fill"></i> Asistente de Instalación</h2>
            <p class="text-muted">Configura tu sistema en pocos pasos</p>
        </div>
        
        <div class="step-indicator" id="step-indicator">
            <div class="step active" id="step1-indicator">
                <div class="step-number">1</div>
                <div class="step-label">Requisitos</div>
            </div>
            <div class="step" id="step2-indicator">
                <div class="step-number">2</div>
                <div class="step-label">Base de Datos</div>
            </div>
            <div class="step" id="step3-indicator">
                <div class="step-number">3</div>
                <div class="step-label">Administrador</div>
            </div>
            <div class="step" id="step4-indicator">
                <div class="step-number">4</div>
                <div class="step-label">Finalizar</div>
            </div>
        </div>
        
        <div class="step-content">
            @yield('content')
        </div>
    </div>
    
    <!-- Bootstrap 5 JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    @stack('scripts')
</body>
</html>
