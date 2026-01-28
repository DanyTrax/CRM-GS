# Solución: Login sin estilos y error de tabla cache

## 🔍 Problemas

1. **Login sin estilos**: Los assets de Filament no están publicados
2. **Error tabla cache**: `Table 'cache' doesn't exist` al hacer login

## ✅ Solución Rápida

Ejecuta estos comandos en cPanel:

```bash
cd ~/services.dowgroupcol.com

# 1. Actualizar código
git pull

# 2. Asegurar que CACHE_DRIVER=file en .env
if ! grep -q "CACHE_DRIVER=file" .env; then
    if grep -q "CACHE_DRIVER=" .env; then
        sed -i 's/CACHE_DRIVER=.*/CACHE_DRIVER=file/' .env
    else
        echo "CACHE_DRIVER=file" >> .env
    fi
fi

# 3. Publicar assets de Filament (comando directo)
php artisan filament:assets

# 4. Limpiar caché
php artisan config:clear
php artisan view:clear
php artisan route:clear

# 5. Verificar que los assets estén publicados
ls -la public/build/assets/ | head -5
```

## 🔧 Si el comando `filament:assets` no existe

Si el comando `filament:assets` no funciona, usa:

```bash
# Publicar assets manualmente
php artisan vendor:publish --tag=filament-assets --force

# O publicar todo de Filament
php artisan vendor:publish --provider="Filament\FilamentServiceProvider" --force
```

## ✅ Verificación

Después de ejecutar los comandos:

1. Recarga la página de login: `https://services.dowgroupcol.com/admin/login`
2. Debería verse con estilos correctos
3. El error de tabla cache debería estar resuelto

## 📋 Si aún hay problemas

Si el login sigue sin estilos:

```bash
# Verificar que los assets estén en public/build
ls -la public/build/assets/

# Si no existen, crear el directorio y publicar
mkdir -p public/build/assets
php artisan filament:assets

# Verificar permisos
chmod -R 755 public/build
```

---

**Nota:** Asegúrate de que `CACHE_DRIVER=file` esté en el `.env` para evitar el error de tabla cache.
