# Comandos para Ejecutar en cPanel

## ⚠️ IMPORTANTE: Filament Shield NO está disponible para v3

El paquete `bezhanov/filament-shield` **solo funciona con FilamentPHP v2**, no con v3.

## ✅ Solución: Usar Sistema de Roles Nativo

El sistema ya tiene:
- ✅ Tabla `roles` con campo `slug`
- ✅ Campo `role_id` en tabla `users`
- ✅ Relación `User->role()`

## 🚀 Comandos para Ejecutar en cPanel

### Opción 1: Script Automático (Recomendado)

```bash
cd ~/services.dowgroupcol.com
git pull
chmod +x comandos-cpanel-v3.sh
./comandos-cpanel-v3.sh
```

### Opción 2: Comandos Manuales

```bash
cd ~/services.dowgroupcol.com

# 1. Actualizar código
git pull

# 2. Instalar dependencias (SIN Shield)
composer install --no-dev --optimize-autoloader

# 3. Publicar Filament
php artisan filament:install --panels

# 4. Publicar Spatie Permission (opcional)
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"

# 5. Ejecutar migraciones
php artisan migrate --force

# 6. Limpiar y optimizar
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 7. Permisos
chmod -R 755 storage bootstrap/cache public
```

## ✅ Verificación

Después de ejecutar los comandos:

```bash
# Verificar que Filament funciona
php artisan filament:list

# Verificar migraciones
php artisan migrate:status

# Verificar configuración
php artisan config:show
```

## 🎯 Acceso

- **Panel Admin:** `https://services.dowgroupcol.com/admin`
- **Instalador:** `https://services.dowgroupcol.com/install` (si no está instalado)

## 📋 Notas

1. **NO ejecutes** `php artisan shield:install` - No existe para v3
2. **NO ejecutes** `composer require bezhanov/filament-shield` - No es compatible
3. El sistema de roles funciona con la tabla `roles` y `role_id` en `users`
4. Los Resources de Filament tienen control de acceso con `canViewAny()`

---

**Última actualización:** He removido Shield del `composer.json` y actualizado los Resources para usar control de acceso nativo.
