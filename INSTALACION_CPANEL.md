# Instalación en cPanel - CRM Services (FilamentPHP)

## 🚀 Pasos para Instalación en cPanel

### Paso 1: Subir Archivos

1. Sube todos los archivos del proyecto a tu directorio en cPanel (ej: `~/services.dowgroupcol.com`)
2. Asegúrate de que el directorio `public` sea el Document Root de tu dominio

### Paso 2: Instalar Dependencias

**IMPORTANTE:** Debes ejecutar esto desde SSH o Terminal de cPanel:

```bash
cd ~/services.dowgroupcol.com
composer install --no-dev --optimize-autoloader
```

Si no tienes acceso SSH, puedes usar el **Terminal de cPanel** o el **File Manager** con ejecución de comandos.

### Paso 3: Configurar Permisos

```bash
chmod -R 755 storage bootstrap/cache
chown -R tu_usuario:tu_grupo storage bootstrap/cache
```

### Paso 4: Acceder al Instalador

1. Abre tu navegador en: `https://services.dowgroupcol.com/install`
2. Completa el wizard de instalación (4 pasos)
3. El instalador ejecutará automáticamente:
   - Creación de `.env`
   - Migraciones
   - Seeders
   - Creación de usuario admin

### Paso 5: Publicar Assets de Filament

**DESPUÉS de completar el instalador**, ejecuta:

```bash
php artisan filament:install --panels
```

Esto publicará los assets de Filament necesarios.

### Paso 6: Instalar Filament Shield (Opcional)

Si quieres usar el sistema de roles con Shield:

```bash
# Primero, asegúrate de que el paquete esté instalado
composer require bezhanov/filament-shield --no-interaction

# Luego publica la configuración
php artisan vendor:publish --tag=filament-shield-config

# Finalmente, instala Shield
php artisan shield:install
php artisan shield:generate --all
```

**NOTA:** Si el comando `shield:install` no existe, significa que el paquete no se instaló correctamente. Verifica que `composer install` se ejecutó sin errores.

## 🔧 Solución de Problemas

### Error: "There are no commands defined in the 'shield' namespace"

**Causa:** Filament Shield no está instalado o no se publicó correctamente.

**Solución:**

1. Verifica que el paquete esté en `composer.json`:
```json
"bezhanov/filament-shield": "^3.0"
```

2. Reinstala dependencias:
```bash
composer remove bezhanov/filament-shield
composer require bezhanov/filament-shield --no-interaction
```

3. Publica la configuración:
```bash
php artisan vendor:publish --tag=filament-shield-config
php artisan vendor:publish --tag=filament-shield-migrations
```

4. Ejecuta migraciones:
```bash
php artisan migrate
```

5. Instala Shield:
```bash
php artisan shield:install
php artisan shield:generate --all
```

### Error: "Class not found" o "ServiceProvider not found"

**Solución:**

1. Limpia caché:
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

2. Regenera autoload:
```bash
composer dump-autoload
```

3. Optimiza:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Error: "Permission denied" en storage

**Solución:**

```bash
chmod -R 775 storage bootstrap/cache
chown -R tu_usuario:tu_grupo storage bootstrap/cache
```

### Filament no carga los estilos

**Solución:**

1. Publica assets:
```bash
php artisan filament:install --panels
```

2. Limpia caché:
```bash
php artisan optimize:clear
```

3. Verifica que `APP_URL` en `.env` sea correcto:
```env
APP_URL=https://services.dowgroupcol.com
```

## 📋 Checklist de Instalación

- [ ] Archivos subidos a cPanel
- [ ] `composer install` ejecutado sin errores
- [ ] Permisos configurados (storage, bootstrap/cache)
- [ ] Wizard de instalación completado (`/install`)
- [ ] `php artisan filament:install --panels` ejecutado
- [ ] (Opcional) `php artisan shield:install` ejecutado
- [ ] Acceso a `/admin` funciona correctamente
- [ ] Login funciona con usuario creado

## 🎯 Acceso Post-Instalación

- **Panel Admin:** `https://services.dowgroupcol.com/admin`
- **Panel Cliente:** `https://services.dowgroupcol.com/portal`
- **Instalador:** `https://services.dowgroupcol.com/install` (solo si no está instalado)

## 📞 Comandos Útiles

```bash
# Verificar estado de la aplicación
php artisan about

# Ver rutas disponibles
php artisan route:list

# Verificar configuración
php artisan config:show

# Limpiar todo
php artisan optimize:clear

# Regenerar autoload
composer dump-autoload
```

---

**Nota:** Si después de seguir estos pasos aún tienes problemas, verifica los logs en `storage/logs/laravel.log`
