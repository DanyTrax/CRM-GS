# 🖥️ Comandos para Ejecutar en SERVIDOR (cPanel)

## 📍 Después de que Subas los Cambios desde LOCAL

### Paso 1: Actualizar Código desde el Repositorio

```bash
cd ~/services.dowgroupcol.com

# Bajar los cambios que subiste desde LOCAL
git pull
```

### Paso 2: Ejecutar Instalación/Configuración

**Opción A: Script Automático (Más Fácil)**

```bash
# Dar permisos de ejecución
chmod +x comandos-cpanel-v3.sh

# Ejecutar script
./comandos-cpanel-v3.sh
```

**Opción B: Comandos Manuales (Si Prefieres Control)**

```bash
# 1. Instalar dependencias (SIN dev dependencies)
composer install --no-dev --optimize-autoloader

# 2. Publicar assets de Filament
php artisan filament:install --panels

# 3. Publicar Spatie Permission (opcional)
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"

# 4. Ejecutar migraciones pendientes
php artisan migrate --force

# 5. Limpiar caché (SIN optimizar - evita error de Collision)
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 6. Configurar permisos
chmod -R 755 storage bootstrap/cache public
```

### Paso 3: Verificar que Funciona

```bash
# Verificar que Filament está instalado
php artisan filament:list

# Verificar rutas
php artisan route:list | head -10

# Verificar estado de migraciones
php artisan migrate:status
```

## ⚠️ Comandos que NO Debes Ejecutar

Estos comandos requieren Collision (solo desarrollo) y fallarán:

```bash
# ❌ NO ejecutar estos:
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize:clear
```

**Razón:** Collision está en `require-dev` y no se instala en producción.

## 🎯 Acceso

Después de ejecutar los comandos:

- **Panel Admin:** `https://services.dowgroupcol.com/admin`
- **Instalador:** `https://services.dowgroupcol.com/install` (si no está instalado)

## 📋 Resumen del Flujo en Servidor

```
1. git pull                    # Bajar cambios
2. ./comandos-cpanel-v3.sh     # Configurar/Instalar
3. ¡Listo!                     # Acceder a /admin
```

## 🔄 Si Necesitas Reinstalar desde Cero

```bash
# Eliminar flag de instalación
rm storage/app/.installed

# Eliminar .env (opcional, si quieres reinstalar)
# mv .env .env.backup

# Acceder a /install en el navegador
# El wizard hará todo automáticamente
```

---

**Nota:** El script `comandos-cpanel-v3.sh` ya evita todos los comandos problemáticos.
