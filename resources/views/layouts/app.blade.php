<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', config('app.name'))</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        @yield('content')
    </div>
    @stack('scripts')
</body>
</html>
