<nav class="navbar">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0">@yield('page-title', 'Dashboard')</h5>
        </div>
        <div class="d-flex align-items-center gap-3">
            <span class="text-muted">
                <i class="bi bi-person-circle"></i> {{ auth()->user()->name ?? 'Usuario' }}
            </span>
            <a href="{{ route('logout') }}" class="btn btn-sm btn-outline-danger">
                <i class="bi bi-box-arrow-right"></i> Salir
            </a>
        </div>
    </div>
</nav>
