# Solución: Error de Permisos en bootstrap/cache

## Error
```
The `/home/dowgroupcol/services.dowgroupcol.com/bootstrap/cache` directory must be present and writable.
```

## Solución Rápida

Ejecuta estos comandos en tu servidor:

```bash
cd ~/services.dowgroupcol.com

# Crear el directorio si no existe
mkdir -p bootstrap/cache

# Dar permisos de escritura
chmod -R 775 bootstrap/cache
# O si 775 no funciona:
chmod -R 777 bootstrap/cache

# Verificar que se creó
ls -la bootstrap/

# Reintentar composer
composer install --no-dev --optimize-autoloader
```

## Solución Completa (Recomendada)

Ejecuta el script de setup que crea toda la estructura:

```bash
cd ~/services.dowgroupcol.com
bash setup-storage.sh
```

Este script crea:
- ✅ `bootstrap/cache` con permisos correctos
- ✅ Toda la estructura de `storage/`
- ✅ Permisos configurados

## Si el error persiste

### Verificar propietario del directorio

```bash
cd ~/services.dowgroupcol.com
ls -la bootstrap/

# Si el propietario no es tu usuario, cambiarlo:
chown -R dowgroupcol:dowgroupcol bootstrap/cache
chown -R dowgroupcol:dowgroupcol storage
```

### Verificar permisos actuales

```bash
cd ~/services.dowgroupcol.com
ls -ld bootstrap/cache
# Debe mostrar algo como: drwxrwxr-x

# Si muestra drwxr-xr-x (sin w para grupo), ejecutar:
chmod 775 bootstrap/cache
```

### Crear manualmente si no existe

```bash
cd ~/services.dowgroupcol.com

# Crear directorio
mkdir -p bootstrap/cache

# Dar permisos
chmod 775 bootstrap/cache

# Verificar
ls -la bootstrap/
# Debe mostrar bootstrap/cache con permisos drwxrwxr-x
```

## Comando Todo-en-Uno

```bash
cd ~/services.dowgroupcol.com && \
mkdir -p bootstrap/cache storage/app/public storage/framework/{cache,sessions,views} storage/logs storage/app/backups && \
chmod -R 775 storage bootstrap/cache && \
composer install --no-dev --optimize-autoloader
```
