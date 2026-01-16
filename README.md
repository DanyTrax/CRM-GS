# CRM - Gestor de Servicios

Sistema ERP/CRM/Billing desarrollado con Laravel 11.

## Requisitos

- PHP 8.2+
- MySQL 8.0+ / MariaDB
- Composer
- Node.js y NPM

## Instalación Rápida

```bash
# Clonar repositorio
git clone https://github.com/DanyTrax/CRM-GS.git
cd CRM-GS

# Instalar dependencias
composer install
npm install

# Configurar entorno
cp .env.example .env
php artisan key:generate

# Publicar migraciones de Spatie
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"

# Configurar base de datos en .env y ejecutar
php artisan migrate --seed

# Compilar assets
npm run build
```

## Scripts de Ayuda

- `setup-storage.sh` - Crea estructura de storage
- `install-pre-deploy.sh` - Instalación previa completa
- `check-install.sh` - Diagnóstico del sistema

## Estructura

```
app/              # Código de la aplicación
config/           # Configuraciones
database/         # Migraciones y seeders
resources/        # Vistas y assets
routes/           # Definición de rutas
public/           # Archivos públicos
```

## Licencia

MIT
