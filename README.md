# CRM - Gestor de Servicios

Sistema ERP/CRM/Billing desarrollado con Laravel 11.

## Requisitos

- **PHP 8.2+** (⚠️ Requerido - Laravel 11 no funciona con PHP 8.1)
- MySQL 8.0+ / MariaDB
- Composer
- Node.js y NPM

### ⚠️ Si tienes PHP 8.1 en el servidor

Debes actualizar PHP a 8.2 o superior:

1. En cPanel, buscar **"Select PHP Version"** o **"MultiPHP Manager"**
2. Seleccionar el dominio `services.dowgroupcol.com`
3. Cambiar a **PHP 8.2** o superior
4. Guardar cambios
5. Verificar: `php -v`

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

# Crear estructura de storage (IMPORTANTE)
bash setup-storage.sh

# Publicar migraciones de Spatie (IMPORTANTE)
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"

# Configurar base de datos en .env y ejecutar
# Editar .env: DB_DATABASE, DB_USERNAME, DB_PASSWORD
php artisan migrate --seed

# Compilar assets
npm run build
```

## ⚠️ Si aparece Error 500

Ver guía completa en `SOLUCION_ERROR_500.md` o ejecutar:

```bash
bash setup-storage.sh
php artisan config:clear
php artisan cache:clear
tail -50 storage/logs/laravel.log
```
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
