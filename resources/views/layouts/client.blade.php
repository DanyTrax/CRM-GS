<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Área de Cliente') - {{ config('app.name') }}</title>
    
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8fafc;
        }
        
        .client-header {
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        
        .client-nav {
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 1rem 0;
            margin-bottom: 2rem;
        }
        
        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <header class="client-header">
        <div class="container">
            <h3 class="mb-0">Área de Cliente</h3>
            <p class="mb-0">Bienvenido, {{ auth()->user()->name ?? 'Cliente' }}</p>
        </div>
    </header>
    
    <nav class="client-nav">
        <div class="container">
            <ul class="nav nav-pills">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('client.dashboard') ? 'active' : '' }}" 
                       href="{{ route('client.dashboard') }}">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('client.services') ? 'active' : '' }}" 
                       href="{{ route('client.services') }}">
                        <i class="bi bi-box-seam"></i> Mis Servicios
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('client.invoices') ? 'active' : '' }}" 
                       href="{{ route('client.invoices') }}">
                        <i class="bi bi-receipt"></i> Mis Facturas
                    </a>
                </li>
            </ul>
        </div>
    </nav>
    
    <div class="container">
        @include('partials.alerts')
        @yield('content')
    </div>
    
    <!-- Bootstrap 5 JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    @stack('scripts')
</body>
</html>
