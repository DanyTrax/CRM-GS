<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel Administrativo') - {{ config('app.name') }}</title>
    
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        :root {
            --sidebar-bg: #1e293b; /* Azul oscuro Enterprise */
            --sidebar-hover: #334155;
            --primary-color: #3b82f6;
            --success-color: #10b981;
            --sidebar-width: 260px;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8fafc;
        }
        
        /* Sidebar Oscuro */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background-color: var(--sidebar-bg);
            color: white;
            padding-top: 20px;
            z-index: 1000;
            overflow-y: auto;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar-brand {
            padding: 1.5rem 1.5rem;
            font-size: 1.5rem;
            font-weight: bold;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 1rem;
            color: white;
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .sidebar-menu li {
            margin: 0;
        }
        
        .sidebar-menu a {
            display: block;
            padding: 0.875rem 1.5rem;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }
        
        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background-color: var(--sidebar-hover);
            color: white;
            border-left-color: var(--primary-color);
            padding-left: 1.75rem;
        }
        
        .sidebar-menu a i {
            width: 24px;
            margin-right: 10px;
        }
        
        /* Contenido Principal */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            padding: 0;
        }
        
        /* Navbar Blanco con Sombra */
        .navbar {
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 1rem 2rem;
            margin-bottom: 2rem;
        }
        
        .navbar .navbar-brand {
            font-weight: 600;
            color: var(--sidebar-bg);
        }
        
        /* Cards con Bordes Suaves */
        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
        }
        
        .card-header {
            background-color: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            font-weight: 600;
            padding: 1rem 1.5rem;
        }
        
        /* Tablas */
        .table {
            margin-bottom: 0;
        }
        
        .table-hover tbody tr:hover {
            background-color: #f1f5f9;
        }
        
        /* Botones */
        .btn-success {
            background-color: var(--success-color);
            border-color: var(--success-color);
        }
        
        .btn-success:hover {
            background-color: #059669;
            border-color: #059669;
        }
        
        /* Badges */
        .badge-health {
            font-size: 0.75rem;
            padding: 0.5rem 0.75rem;
        }
        
        .status-healthy {
            background-color: var(--success-color);
            color: white;
        }
        
        .status-warning {
            background-color: #f59e0b;
            color: white;
        }
        
        .status-danger {
            background-color: #ef4444;
            color: white;
        }
        
        /* Contenedor de Contenido */
        .content-wrapper {
            padding: 2rem;
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Sidebar -->
    @include('partials.sidebar')
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Navbar -->
        @include('partials.navbar')
        
        <!-- Content Wrapper -->
        <div class="content-wrapper">
            <!-- Alerts -->
            @include('partials.alerts')
            
            <!-- Page Content -->
            @yield('content')
        </div>
    </div>
    
    <!-- Bootstrap 5 JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Vanilla JS Utilities -->
    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        async function fetchJSON(url, options = {}) {
            const defaultOptions = {
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                ...options,
            };
            
            const response = await fetch(url, defaultOptions);
            return response.json();
        }
        
        // Auto-dismiss alerts
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
        });
    </script>
    
    @stack('scripts')
</body>
</html>
