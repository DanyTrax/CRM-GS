# CRM - Gestor de Servicios

Sistema ERP/CRM/Billing completo desarrollado con Laravel 11.

## Características Principales

- ✅ Sistema de Roles y Permisos Dinámicos (ACL)
- ✅ Gestión de Clientes (Persona Natural/Jurídica)
- ✅ Módulo de Servicios con Renovación Automática
- ✅ Facturación Multimoneda (COP/USD) con Integración Bold
- ✅ Sistema de Comunicaciones con Interceptor de Correos
- ✅ Área de Cliente con Portal Web
- ✅ Sistema de Backups Automáticos
- ✅ Wizard de Instalación
- ✅ Panel de Salud y Monitoreo
- ✅ 2FA (Google Authenticator)
- ✅ Impersonation de Usuarios

## Requisitos

- PHP 8.2 o superior
- MySQL 8.0 o MariaDB equivalente
- Composer
- Node.js y NPM (para assets)

## Instalación

1. Clonar el repositorio:
```bash
git clone https://github.com/DanyTrax/CRM-GS.git
cd CRM-GS
```

2. Instalar dependencias:
```bash
composer install
npm install
```

3. Configurar entorno:
```bash
cp .env.example .env
php artisan key:generate
```

4. Publicar migraciones de Spatie Permission:
```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
```

5. Acceder al wizard de instalación:
```
http://tu-dominio.com/install
```

O configurar manualmente la base de datos en `.env` y ejecutar:
```bash
php artisan migrate --seed
```

6. Compilar assets:
```bash
npm run build
```

## Configuración de Colas

Para procesar correos y tareas en segundo plano, configurar Supervisor o Cron:

### Opción 1: Supervisor (Recomendado)
```bash
php artisan queue:work --tries=3
```

### Opción 2: Cron
```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

## Estructura del Proyecto

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/        # Controladores del panel admin
│   │   ├── Client/       # Controladores del área de cliente
│   │   └── Install/      # Wizard de instalación
│   ├── Middleware/       # Middleware personalizado
│   └── Requests/         # Form Requests
├── Models/               # Modelos Eloquent
├── Services/             # Servicios de negocio
├── Jobs/                 # Jobs para colas
├── Mail/                 # Clases de correo
└── Policies/             # Policies de autorización

database/
├── migrations/           # Migraciones de BD
└── seeders/              # Seeders de datos iniciales

resources/
├── views/
│   ├── admin/           # Vistas del panel admin
│   ├── client/          # Vistas del área de cliente
│   └── install/         # Vistas del wizard
└── css/                 # Estilos Tailwind

public/                  # Archivos públicos
```

## Roles Pre-configurados

1. **Super Administrador**: Acceso total
2. **Administrador Operativo**: Gestión operativa (sin configuración global)
3. **Contador**: Solo lectura financiera
4. **Soporte**: Gestión de tickets (sin información financiera)
5. **Cliente**: Acceso a su propia información

## Licencia

MIT
