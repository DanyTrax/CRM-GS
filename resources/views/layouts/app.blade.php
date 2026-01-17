<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    @vite(['resources/css/app.css'])
    @if(app()->environment('production'))
        <!-- Fallback CSS básico si Vite falla -->
        <style>
            body { font-family: system-ui, -apple-system, sans-serif; margin: 0; padding: 0; }
            .container { max-width: 1200px; margin: 0 auto; padding: 1rem; }
            .bg-white { background-color: white; }
            .bg-gray-100 { background-color: #f3f4f6; }
            .rounded-lg { border-radius: 0.5rem; }
            .shadow { box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
            .px-4 { padding-left: 1rem; padding-right: 1rem; }
            .py-8 { padding-top: 2rem; padding-bottom: 2rem; }
            .text-3xl { font-size: 1.875rem; }
            .font-bold { font-weight: 700; }
            .mb-8 { margin-bottom: 2rem; }
            button, .btn { background-color: #3b82f6; color: white; padding: 0.5rem 1rem; border-radius: 0.25rem; border: none; cursor: pointer; }
            button:hover { background-color: #2563eb; }
            input[type="text"], input[type="email"], input[type="password"], textarea, select { width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.25rem; }
        </style>
    @endif
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        @yield('content')
    </div>
    @stack('scripts')
</body>
</html>
