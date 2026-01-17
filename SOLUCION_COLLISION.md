# Solución: Error CollisionServiceProvider no encontrado

## Problema
```
Class "NunoMaduro\Collision\Adapters\Laravel\CollisionServiceProvider" not found
Class Illuminate\Foundation\Composer\Scripts is not autoloadable
```

## Solución Rápida

Ejecuta estos comandos en orden:

```bash
cd ~/services.dowgroupcol.com

# 1. Limpiar todo
rm -rf vendor/
rm -rf bootstrap/cache/*
rm -rf storage/framework/cache/*
rm composer.lock  # Si existe

# 2. Reinstalar dependencias
composer install --no-dev --optimize-autoloader

# 3. Si sigue fallando, instalar sin optimización primero
composer install --no-dev

# 4. Regenerar autoload manualmente
composer dump-autoload --optimize

# 5. Limpiar caché de Laravel
php artisan config:clear
php artisan cache:clear
```

## Solución Alternativa (Si la anterior no funciona)

```bash
cd ~/services.dowgroupcol.com

# 1. Eliminar vendor y composer.lock
rm -rf vendor/ composer.lock

# 2. Actualizar composer
composer self-update

# 3. Instalar desde cero sin --no-dev primero
composer install

# 4. Luego optimizar
composer install --no-dev --optimize-autoloader
```

## Verificar que funciona

```bash
cd ~/services.dowgroupcol.com

# Verificar que vendor existe
ls -la vendor/

# Verificar que autoload existe
ls -la vendor/autoload.php

# Probar artisan
php artisan --version

# Si funciona, limpiar caché
php artisan config:clear
php artisan cache:clear
```

## Si el problema persiste

**Verificar versión de Composer:**
```bash
composer --version
# Debe ser 2.x o superior
```

**Si Composer es muy viejo, actualizarlo:**
```bash
composer self-update
```

**Reinstalar completamente:**
```bash
cd ~/services.dowgroupcol.com

# Eliminar todo
rm -rf vendor/ composer.lock bootstrap/cache/* storage/framework/cache/*

# Reinstalar
composer install --no-dev

# Optimizar después
composer dump-autoload --optimize --no-dev
```
