<nav class="sidebar">
    <div class="sidebar-brand">
        <i class="bi bi-gear-fill"></i> CRM Services
    </div>
    
    <ul class="sidebar-menu">
        <li>
            <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
        </li>
        <li>
            <a href="{{ route('admin.clients.index') }}" class="{{ request()->routeIs('admin.clients.*') ? 'active' : '' }}">
                <i class="bi bi-people"></i> Clientes
            </a>
        </li>
        <li>
            <a href="{{ route('admin.services.index') }}" class="{{ request()->routeIs('admin.services.*') ? 'active' : '' }}">
                <i class="bi bi-box-seam"></i> Servicios
            </a>
        </li>
        <li>
            <a href="{{ route('admin.invoices.index') }}" class="{{ request()->routeIs('admin.invoices.*') ? 'active' : '' }}">
                <i class="bi bi-receipt"></i> Facturas
            </a>
        </li>
        <li>
            <a href="{{ route('admin.payments.index') }}" class="{{ request()->routeIs('admin.payments.*') ? 'active' : '' }}">
                <i class="bi bi-credit-card"></i> Pagos
            </a>
        </li>
        
        @if(auth()->user() && auth()->user()->role && auth()->user()->role->slug === 'super-admin')
        <li>
            <a href="{{ route('admin.settings.index') }}" class="{{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                <i class="bi bi-gear"></i> Configuración
            </a>
        </li>
        @endif
    </ul>
</nav>
