# Solución Final: Error 500 en Todas las Páginas

## Diagnóstico Rápido

Ejecuta el script de diagnóstico:

```bash
cd ~/services.dowgroupcol.com
bash diagnostico-500.sh
```

O manualmente:

```bash
cd ~/services.dowgroupcol.com

# Ver los últimos errores en el log
tail -50 storage/logs/laravel.log
```

**El log mostrará el error exacto.** Comparte las últimas 20-30 líneas del log.

---

## Soluciones Comunes

### 1. Ver Logs de Error (MUY IMPORTANTE)

```bash
cd ~/services.dowgroupcol.com
tail -50 storage/logs/laravel.log
```

**Busca errores como:**
- `Class not found`
- `View not found`
- `SQLSTATE`
- `Permission denied`

---

### 2. Verificar que Vistas Existen

```bash
cd ~/services.dowgroupcol.com

# Verificar que existen vistas básicas
ls -la resources/views/layouts/app.blade.php
ls -la resources/views/install/index.blade.php

# Si no existen, crear una vista simple de prueba
```

---

### 3. Limpiar TODA la Caché

```bash
cd ~/services.dowgroupcol.com

# Limpiar todo tipo de caché
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Limpiar manualmente
rm -rf bootstrap/cache/*
rm -rf storage/framework/cache/*
rm -rf storage/framework/views/*
```

---

### 4. Verificar APP_KEY

```bash
cd ~/services.dowgroupcol.com

# Verificar APP_KEY
grep APP_KEY .env

# Si no está configurado o está vacío:
php artisan key:generate
```

---

### 5. Verificar Permisos de Storage

```bash
cd ~/services.dowgroupcol.com

# Dar permisos completos
chmod -R 775 storage bootstrap/cache
chown -R dowgroupcol:dowgroupcol storage bootstrap/cache

# Si 775 no funciona, probar 777
chmod -R 777 storage bootstrap/cache
```

---

### 6. Verificar Base de Datos

```bash
cd ~/services.dowgroupcol.com

# Probar conexión a base de datos
php artisan tinker
# Dentro de tinker:
DB::connection()->getPdo();
# Si funciona, verás: PDO {#123 ...}
# Si da error, hay problema de conexión
exit
```

---

### 7. Revisar Error Específico del Log

**El paso más importante es ver el log:**

```bash
cd ~/services.dowgroupcol.com

# Ver últimos 50 errores
tail -50 storage/logs/laravel.log | grep -i error

# O ver todo el log
tail -100 storage/logs/laravel.log
```

**Comparte las últimas líneas del log que muestren el error.**

---

## Comandos Todo-en-Uno

Ejecuta estos comandos en orden:

```bash
cd ~/services.dowgroupcol.com && \
php artisan config:clear && \
php artisan cache:clear && \
php artisan route:clear && \
php artisan view:clear && \
rm -rf bootstrap/cache/* storage/framework/cache/* storage/framework/views/* && \
chmod -R 775 storage bootstrap/cache && \
php artisan key:generate && \
tail -50 storage/logs/laravel.log
```

**El último comando (`tail -50`) te mostrará el error específico.**

---

## Si el Problema Persiste

**Comparte el output de estos comandos:**

```bash
cd ~/services.dowgroupcol.com

# 1. Últimos errores del log
tail -100 storage/logs/laravel.log

# 2. Verificar rutas
php artisan route:list | head -10

# 3. Verificar estructura
ls -la index.php .htaccess
ls -la app/Http/Controllers/Controller.php
```

Esto ayudará a identificar el problema exacto.
