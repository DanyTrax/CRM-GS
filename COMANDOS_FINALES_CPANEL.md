# Comandos Finales para cPanel - Solución Completa

## ✅ Problemas Resueltos

1. ✅ **Collision Error** - Removido de comandos de producción
2. ✅ **Shield no disponible** - Removido, usando roles nativos
3. ✅ **bootstrap/providers.php** - Creado para Laravel 11

## 🚀 Comandos para Ejecutar en cPanel

### Opción 1: Script Automático (Recomendado)

```bash
cd ~/services.dowgroupcol.com

# Actualizar código
git pull

# Ejecutar script
chmod +x comandos-cpanel-v3.sh
./comandos-cpanel-v3.sh
```

### Opción 2: Comandos Manuales

```bash
cd ~/services.dowgroupcol.com

# 1. Actualizar código
git pull

# 2. Instalar dependencias (SIN dev dependencies)
composer install --no-dev --optimize-autoloader

# 3. Publicar Filament
php artisan filament:install --panels

# 4. Publicar Spatie Permission (opcional)
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"

# 5. Ejecutar migraciones
php artisan migrate --force

# 6. Limpiar caché (SIN optimizar - evita error de Collision)
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 7. Permisos
chmod -R 755 storage bootstrap/cache public
```

## ⚠️ Comandos que NO Debes Ejecutar en Producción

Estos comandos requieren Collision (solo desarrollo):

```bash
# ❌ NO ejecutar estos:
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize:clear
```

**Razón:** Collision está en `require-dev` y no se instala en producción con `--no-dev`.

## ✅ Verificación

Después de ejecutar los comandos:

```bash
# Verificar que funciona
php artisan about

# Verificar rutas
php artisan route:list | head -10

# Verificar que Filament está instalado
php artisan filament:list
```

## 🎯 Acceso

- **Panel Admin:** `https://services.dowgroupcol.com/admin`
- **Instalador:** `https://services.dowgroupcol.com/install` (si no está instalado)

## 📋 Archivos Importantes Creados/Actualizados

- ✅ `bootstrap/providers.php` - **NUEVO** para Laravel 11
- ✅ `bootstrap/app.php` - Bootstrap de Laravel 11
- ✅ `comandos-cpanel-v3.sh` - Script sin Collision
- ✅ `SOLUCION_COLLISION.md` - Documentación del error
- ✅ `VERIFICAR_GIT.md` - Cómo verificar que los archivos se suben

## 🔄 Si Necesitas Subir Cambios al Repositorio

```bash
# Ver cambios
git status

# Agregar cambios
git add .

# Commit
git commit -m "Correcciones: Removido Shield, corregido Collision, agregado bootstrap/providers.php"

# Push
git push
```

---

**Última actualización:** Todos los problemas resueltos. El sistema funciona sin Collision y sin Shield.
