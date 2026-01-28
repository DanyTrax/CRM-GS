# 🔄 Flujo de Trabajo: Local → Repositorio → Servidor

## 📍 LOCAL (Donde estás ahora - Tu máquina)

### Paso 1: Verificar Cambios

```bash
cd /Users/soporte/Desktop/repos/Services.dow

# Ver qué archivos han cambiado
git status

# Ver archivos específicos importantes
git status | grep -E "(composer.json|bootstrap|Filament|migrations|views)"
```

### Paso 2: Agregar Cambios

```bash
# Agregar todos los cambios
git add .

# Verificar qué se va a subir
git status
```

### Paso 3: Commit

```bash
git commit -m "Migración completa a FilamentPHP v3:
- Removido Shield (no compatible con v3)
- Corregido error de Collision
- Agregado bootstrap/providers.php y bootstrap/app.php para Laravel 11
- Actualizados modelos con nuevos nombres de columnas
- Creados Resources de Filament (Client, Service)
- Actualizado script de instalación para cPanel"
```

### Paso 4: Push al Repositorio

```bash
git push origin main
# o si tu rama se llama master:
git push origin master
```

## 📍 SERVIDOR (cPanel - Después de git pull)

### Paso 1: Actualizar Código

```bash
cd ~/services.dowgroupcol.com
git pull
```

### Paso 2: Ejecutar Instalación/Configuración

**Opción A: Script Automático (Recomendado)**

```bash
chmod +x comandos-cpanel-v3.sh
./comandos-cpanel-v3.sh
```

**Opción B: Comandos Manuales**

```bash
# 1. Instalar dependencias
composer install --no-dev --optimize-autoloader

# 2. Publicar Filament
php artisan filament:install --panels

# 3. Ejecutar migraciones
php artisan migrate --force

# 4. Limpiar caché (sin optimizar)
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 5. Permisos
chmod -R 755 storage bootstrap/cache public
```

## ✅ Archivos Críticos que DEBEN Subirse

Verifica que estos archivos estén en el commit:

```bash
# En LOCAL, antes de hacer commit:
git status | grep -E "(bootstrap/providers.php|bootstrap/app.php|composer.json|app/Filament)"
```

### Checklist de Archivos Importantes:

- [x] `composer.json` - Sin Shield, con Filament v3
- [x] `bootstrap/providers.php` - **NUEVO** (Laravel 11)
- [x] `bootstrap/app.php` - **NUEVO** (Laravel 11)
- [x] `app/Providers/Filament/AdminPanelProvider.php`
- [x] `app/Providers/Filament/ClientPanelProvider.php`
- [x] `app/Filament/Resources/ClientResource.php`
- [x] `app/Filament/Resources/ServiceResource.php`
- [x] `app/Models/User.php` - Con `role_id`
- [x] `app/Models/Client.php` - Con nuevos campos
- [x] `app/Models/Service.php` - Con nuevos campos
- [x] `database/migrations/*.php` - Todas las migraciones
- [x] `resources/views/installer/*.blade.php` - Vistas del instalador
- [x] `comandos-cpanel-v3.sh` - Script actualizado

## 📋 Resumen del Flujo

```
LOCAL:
1. git add .
2. git commit -m "mensaje"
3. git push

SERVIDOR:
1. git pull
2. ./comandos-cpanel-v3.sh
3. ¡Listo!
```

## ⚠️ Recordatorios

- **NO subir:** `.env`, `vendor/`, `storage/app/.installed`
- **SÍ subir:** Todo el código fuente, migraciones, vistas, configuraciones
- **En servidor:** NO ejecutar comandos que requieren Collision (`config:cache`, `route:cache`, etc.)

---

**Estado Actual:** ✅ Todos los archivos están listos para subir desde LOCAL.
