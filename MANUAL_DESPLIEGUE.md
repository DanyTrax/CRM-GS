# Manual de Despliegue - CRM-GS

## Requisitos Previos

- PHP 8.2 o superior
- MySQL 8.0 o MariaDB equivalente
- Composer
- Node.js y NPM
- Servidor web (Apache/Nginx)
- Acceso SSH o cPanel

## Instalación en cPanel

### Paso 1: Clonar o Subir Archivos

#### Opción A: Clonar desde Git (Recomendado)

Desde SSH:

```bash
cd ~
git clone https://github.com/DanyTrax/CRM-GS.git services.dowgroupcol.com
cd services.dowgroupcol.com
```

#### Opción B: Subir Archivos Manualmente

1. Comprimir el proyecto completo (excepto `node_modules` y `vendor`)
2. Subir el archivo ZIP a cPanel
3. Extraer en la carpeta raíz `services.dowgroupcol.com`

### Paso 2: Configurar Estructura de Carpetas

**Estructura en el servidor:**
```
services.dowgroupcol.com/
├── index.php (desde public/index.php)
├── .htaccess
├── app/
├── bootstrap/
├── config/
├── database/
├── resources/
├── routes/
├── storage/
└── vendor/
```

**IMPORTANTE**: Mover el contenido de la carpeta `public/` a la raíz `services.dowgroupcol.com/`:
- Mover `public/index.php` → `services.dowgroupcol.com/index.php`
- Mover `public/.htaccess` → `services.dowgroupcol.com/.htaccess`
- Actualizar la ruta en `index.php` (ver Paso 3)

### Paso 3: Actualizar index.php

Editar `services.dowgroupcol.com/index.php` y cambiar las rutas:

```php
<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
(require_once __DIR__.'/bootstrap/app.php')
    ->handleRequest(Request::capture());
```

### Paso 4: Crear Estructura de Storage

**IMPORTANTE**: Si clonaste desde Git, la carpeta `storage` no estará en el repositorio (está en `.gitignore`). Debes crearla manualmente.

#### Opción 1: Usar Script Automático (Recomendado)

Desde SSH:

```bash
cd ~/services.dowgroupcol.com
bash setup-storage.sh
```

Este script creará automáticamente toda la estructura necesaria y configurará los permisos.

#### Opción 2: Crear Manualmente desde SSH

```bash
cd ~/services.dowgroupcol.com

# Crear estructura de carpetas de storage
mkdir -p storage/app/public
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
mkdir -p storage/app/backups
mkdir -p bootstrap/cache

# Crear archivo .gitkeep en carpetas vacías (opcional, para mantener estructura)
touch storage/app/.gitkeep
touch storage/app/public/.gitkeep
touch storage/framework/cache/.gitkeep
touch storage/framework/sessions/.gitkeep
touch storage/framework/views/.gitkeep
touch storage/logs/.gitkeep
touch bootstrap/cache/.gitkeep
```

#### Opción 3: Desde File Manager de cPanel

1. Navegar a `services.dowgroupcol.com`
2. Crear las siguientes carpetas manualmente:
   - `storage/app/public`
   - `storage/framework/cache`
   - `storage/framework/sessions`
   - `storage/framework/views`
   - `storage/logs`
   - `storage/app/backups`
   - `bootstrap/cache`

### Paso 5: Configurar Permisos

Desde SSH o File Manager de cPanel:

```bash
cd ~/services.dowgroupcol.com

# Dar permisos de escritura a storage y cache
chmod -R 775 storage
chmod -R 775 bootstrap/cache
chmod 644 .env

# Si los permisos no funcionan, intentar con 755
chmod -R 755 storage
chmod -R 755 bootstrap/cache
```

**Nota**: Si usas File Manager, hacer clic derecho en cada carpeta → "Change Permissions" → Marcar "Write" para el propietario.

### Paso 6: Instalar Dependencias

```bash
cd ~/services.dowgroupcol.com
composer install --no-dev --optimize-autoloader
npm install
npm run build
```

### Paso 7: Configurar Base de Datos

1. Crear base de datos desde cPanel
2. Crear usuario y asignar permisos
3. Acceder a `https://services.dowgroupcol.com/install` para configurar automáticamente
   O configurar manualmente en `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=nombre_base_datos
DB_USERNAME=usuario_bd
DB_PASSWORD=contraseña_bd
```

### Paso 8: Ejecutar Instalación

1. Acceder a `https://services.dowgroupcol.com/install`
2. Seguir el wizard de instalación:
   - Verificar requisitos
   - Configurar base de datos
   - Crear usuario administrador
   - Ejecutar migraciones

**Nota**: Si el wizard muestra error sobre permisos de `storage`, volver al Paso 5 y verificar permisos.

### Paso 9: Configurar .htaccess

Crear/editar `.htaccess` en `services.dowgroupcol.com/`:

```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

## Configuración de Cron Jobs

### Opción 1: Desde cPanel

1. Ir a "Cron Jobs" en cPanel
2. Crear nueva tarea:
   - Frecuencia: `* * * * *` (cada minuto)
   - Comando: `cd /home/usuario/services.dowgroupcol.com && php artisan schedule:run >> /dev/null 2>&1`

   **Nota**: Reemplazar `usuario` con tu usuario de cPanel. Para encontrar tu usuario, ejecuta `whoami` desde SSH o revisa la ruta en File Manager.

### Opción 2: Desde SSH

```bash
crontab -e
```

Agregar:
```
* * * * * cd /home/usuario/services.dowgroupcol.com && php artisan schedule:run >> /dev/null 2>&1
```

**Ejemplo con usuario real:**
```
* * * * * cd /home/dowgroup/services.dowgroupcol.com && php artisan schedule:run >> /dev/null 2>&1
```

## Configuración de Colas (Queues)

### Opción 1: Supervisor (Recomendado para VPS)

Instalar Supervisor:
```bash
sudo apt-get install supervisor
```

Crear archivo `/etc/supervisor/conf.d/crm-gs-worker.conf`:

```ini
[program:crm-gs-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /home/usuario/services.dowgroupcol.com/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/home/usuario/services.dowgroupcol.com/storage/logs/worker.log
stopwaitsecs=3600
```

**Nota**: Reemplazar `usuario` con tu usuario de cPanel.

Iniciar Supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start crm-gs-worker:*
```

### Opción 2: Cron para Colas (cPanel)

Crear cron job adicional en cPanel:
```
* * * * * cd /home/usuario/services.dowgroupcol.com && php artisan queue:work --once >> /dev/null 2>&1
```

**Ejemplo con usuario real:**
```
* * * * * cd /home/dowgroup/services.dowgroupcol.com && php artisan queue:work --once >> /dev/null 2>&1
```

## Configuración de Correo (SMTP)

Editar `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.zoho.com
MAIL_PORT=587
MAIL_USERNAME=tu-email@zoho.com
MAIL_PASSWORD=tu-contraseña
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@services.dowgroupcol.com
MAIL_FROM_NAME="CRM-GS"
```

## Configuración de Bold

Editar `.env`:

```env
BOLD_API_KEY=tu-api-key
BOLD_API_SECRET=tu-api-secret
BOLD_WEBHOOK_SECRET=tu-webhook-secret
BOLD_ENVIRONMENT=production
```

## Configuración de Backups

### Google Drive

1. Obtener token de OAuth2 de Google
2. Editar `.env`:

```env
BACKUP_DRIVE_TOKEN=tu-token-google-drive
BACKUP_RETENTION_DAYS=30
```

### OneDrive

1. Obtener token de Microsoft
2. Editar `.env`:

```env
BACKUP_ONEDRIVE_TOKEN=tu-token-onedrive
```

## Optimizaciones Post-Instalación

Desde SSH o Terminal de cPanel:

```bash
cd ~/services.dowgroupcol.com

# Cachear configuración
php artisan config:cache

# Cachear rutas
php artisan route:cache

# Cachear vistas
php artisan view:cache

# Optimizar autoloader
composer dump-autoload -o
```

## Verificación

1. Acceder a `/admin` y verificar login
2. Verificar que las colas funcionan: `/admin/health`
3. Probar creación de backup: `/admin/backups`
4. Verificar cron jobs en logs: `storage/logs/laravel.log`

## Solución de Problemas

### Error 500

- Verificar permisos de `storage/` y `bootstrap/cache/`
- Revisar logs en `storage/logs/laravel.log`
- Verificar que `.env` existe y está configurado

### Colas no procesan

- Verificar que Supervisor está corriendo
- Revisar `storage/logs/worker.log`
- Verificar conexión a base de datos

### Cron no ejecuta

- Verificar sintaxis del cron job
- Verificar permisos de ejecución
- Revisar logs del servidor

## Seguridad

1. Cambiar permisos de `.env` a 600
2. No exponer archivos sensibles en `public/`
3. Mantener Laravel actualizado
4. Configurar firewall
5. Usar HTTPS

## Actualización

```bash
cd ~/services.dowgroupcol.com
git pull origin main
composer install --no-dev
php artisan migrate
php artisan config:cache
php artisan route:cache
php artisan view:cache
npm run build
```

## Estructura Final del Proyecto

```
/home/usuario/services.dowgroupcol.com/
├── index.php                    # Punto de entrada (desde public/)
├── .htaccess                    # Configuración Apache
├── .env                         # Variables de entorno
├── artisan                      # CLI de Laravel
├── composer.json
├── package.json
├── app/                         # Código de la aplicación
│   ├── Http/
│   ├── Models/
│   ├── Services/
│   └── ...
├── bootstrap/
│   └── app.php
├── config/                      # Archivos de configuración
├── database/
│   ├── migrations/
│   └── seeders/
├── public/                      # (No se usa, contenido movido a raíz)
├── resources/
│   ├── views/
│   └── css/
├── routes/                      # Definición de rutas
├── storage/                     # Archivos de almacenamiento
│   ├── app/
│   ├── framework/
│   └── logs/
└── vendor/                      # Dependencias de Composer
```

## Notas Importantes para services.dowgroupcol.com

1. **Ruta Completa**: La ruta completa será `/home/[tu-usuario]/services.dowgroupcol.com/`
   - Para encontrar tu usuario: ejecuta `whoami` desde SSH
   - O revisa la ruta en File Manager de cPanel

2. **Dominio**: Asegúrate de que el dominio `services.dowgroupcol.com` esté configurado en cPanel para apuntar a esta carpeta.

3. **PHP Version**: Verificar que PHP 8.2+ esté seleccionado en "Select PHP Version" de cPanel.

4. **Composer**: Si no está instalado, usar "Software" → "Setup Node.js App" o instalar desde SSH.

5. **Node.js**: Para compilar assets, asegúrate de tener Node.js disponible en cPanel.
