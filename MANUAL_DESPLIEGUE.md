# Manual de Despliegue - CRM-GS

## Requisitos Previos

- PHP 8.2 o superior
- MySQL 8.0 o MariaDB equivalente
- Composer
- Node.js y NPM
- Servidor web (Apache/Nginx)
- Acceso SSH o cPanel

## Instalación en cPanel

### Paso 1: Subir Archivos

1. Comprimir el proyecto completo (excepto `node_modules` y `vendor`)
2. Subir el archivo ZIP a cPanel
3. Extraer en la carpeta `public_html` o subdirectorio

### Paso 2: Configurar Estructura de Carpetas

Si el proyecto está en `public_html/crm-gs/`, mover el contenido de `public/` a `public_html/crm-gs/` y actualizar rutas.

**Estructura recomendada:**
```
public_html/
├── index.php (desde public/index.php)
├── .htaccess
└── crm-gs/
    ├── app/
    ├── bootstrap/
    ├── config/
    ├── database/
    ├── resources/
    ├── routes/
    ├── storage/
    └── vendor/
```

### Paso 3: Configurar Permisos

```bash
chmod -R 755 storage bootstrap/cache
chmod -R 775 storage
```

### Paso 4: Instalar Dependencias

```bash
cd public_html/crm-gs
composer install --no-dev --optimize-autoloader
npm install
npm run build
```

### Paso 5: Configurar Base de Datos

1. Crear base de datos desde cPanel
2. Crear usuario y asignar permisos
3. Acceder a `/install` para configurar automáticamente
   O configurar manualmente en `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=nombre_base_datos
DB_USERNAME=usuario_bd
DB_PASSWORD=contraseña_bd
```

### Paso 6: Ejecutar Instalación

1. Acceder a `https://tu-dominio.com/install`
2. Seguir el wizard de instalación:
   - Verificar requisitos
   - Configurar base de datos
   - Crear usuario administrador
   - Ejecutar migraciones

### Paso 7: Configurar .htaccess

Crear/editar `.htaccess` en `public_html/`:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

O si el proyecto está directamente en `public_html/`:

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
   - Comando: `cd /home/usuario/public_html/crm-gs && php artisan schedule:run >> /dev/null 2>&1`

### Opción 2: Desde SSH

```bash
crontab -e
```

Agregar:
```
* * * * * cd /ruta/completa/al/proyecto && php artisan schedule:run >> /dev/null 2>&1
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
command=php /ruta/completa/al/proyecto/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/ruta/completa/al/proyecto/storage/logs/worker.log
stopwaitsecs=3600
```

Iniciar Supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start crm-gs-worker:*
```

### Opción 2: Cron para Colas (cPanel)

Crear cron job adicional:
```
* * * * * cd /home/usuario/public_html/crm-gs && php artisan queue:work --once >> /dev/null 2>&1
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
MAIL_FROM_ADDRESS=noreply@tu-dominio.com
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

```bash
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
git pull origin main
composer install --no-dev
php artisan migrate
php artisan config:cache
php artisan route:cache
php artisan view:cache
npm run build
```
