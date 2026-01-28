# Solución: Error 500 Internal Server Error

## Diagnóstico Rápido

Ejecuta estos comandos en orden para diagnosticar el problema:

```bash
cd ~/services.dowgroupcol.com

# 1. Verificar que existe .env
ls -la .env
# Si no existe:
cp .env.example .env
php artisan key:generate

# 2. Verificar permisos de storage y bootstrap/cache
ls -la storage/
ls -la bootstrap/cache
# Si no existen o no tienen permisos:
bash setup-storage.sh

# 3. Ver logs de error
tail -50 storage/logs/laravel.log

# 4. Verificar que vendor existe
ls -la vendor/

# 5. Limpiar caché
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

## Causas Comunes y Soluciones

### 1. Falta archivo .env o APP_KEY

```bash
cd ~/services.dowgroupcol.com

# Verificar que .env existe
if [ ! -f .env ]; then
    cp .env.example .env
    php artisan key:generate
fi

# Verificar que APP_KEY está configurado
grep APP_KEY .env
# Debe mostrar algo como: APP_KEY=base64:...
```

### 2. Permisos incorrectos en storage o bootstrap/cache

```bash
cd ~/services.dowgroupcol.com

# Ejecutar script de setup (recomendado)
bash setup-storage.sh

# O manualmente:
mkdir -p storage/app/public storage/framework/{cache,sessions,views} storage/logs bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

### 3. Base de datos no configurada

```bash
cd ~/services.dowgroupcol.com

# Verificar .env tiene configuración de BD
grep DB_ .env

# Debe mostrar:
# DB_CONNECTION=mysql
# DB_HOST=localhost
# DB_DATABASE=nombre_bd
# DB_USERNAME=usuario
# DB_PASSWORD=contraseña

# Si no está configurado, editar .env:
nano .env
```

### 4. Migraciones no ejecutadas

```bash
cd ~/services.dowgroupcol.com

# Publicar migraciones de Spatie (IMPORTANTE)
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"

# Ejecutar migraciones
php artisan migrate --seed
```

### 5. Errores en los logs

```bash
cd ~/services.dowgroupcol.com

# Ver últimos errores
tail -100 storage/logs/laravel.log

# Los errores más comunes:
# - "No application encryption key": Ejecutar php artisan key:generate
# - "Class X not found": Ejecutar composer install
# - "Permission denied": Revisar permisos de storage
# - "SQLSTATE": Verificar configuración de base de datos
```

## Pasos Completos para Solucionar Error 500

Ejecuta estos comandos en orden:

```bash
cd ~/services.dowgroupcol.com

# Paso 1: Verificar .env
if [ ! -f .env ]; then
    cp .env.example .env
    php artisan key:generate
fi

# Paso 2: Crear estructura de storage
bash setup-storage.sh

# Paso 3: Limpiar caché
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Paso 4: Verificar logs
tail -20 storage/logs/laravel.log

# Paso 5: Intentar acceder nuevamente
# Si sigue dando error, revisar el log específico
```

## Verificación Final

Después de ejecutar los pasos, verifica:

```bash
cd ~/services.dowgroupcol.com

# 1. .env existe y tiene APP_KEY
grep APP_KEY .env | grep -v "^#"

# 2. Storage tiene permisos
ls -ld storage bootstrap/cache
# Debe mostrar: drwxrwxr-x o drwxrwxrwx

# 3. Vendor existe
ls -d vendor

# 4. No hay errores recientes en logs
tail -5 storage/logs/laravel.log | grep -i error
```

## Si el problema persiste

Comparte el output de:

```bash
cd ~/services.dowgroupcol.com
tail -50 storage/logs/laravel.log
```

Esto mostrará el error exacto que está causando el 500.
