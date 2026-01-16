# Solución: Composer detecta PHP incorrecto

## Problema
- `php -v` muestra PHP 8.2.29 ✅
- Pero `composer install` detecta PHP 8.1.33 ❌

## Solución 1: Especificar PHP explícitamente para Composer

Ejecutar Composer con la ruta completa de PHP 8.2:

```bash
cd ~/services.dowgroupcol.com

# Encontrar la ruta de PHP 8.2
which php
# O buscar todas las versiones
ls -la /usr/bin/php* 
# O en cPanel suele estar en:
# /opt/cpanel/ea-php82/root/usr/bin/php

# Usar PHP 8.2 explícitamente
/opt/cpanel/ea-php82/root/usr/bin/php /usr/local/bin/composer install --no-dev --optimize-autoloader

# O si composer está en otra ubicación:
/opt/cpanel/ea-php82/root/usr/bin/php $(which composer) install --no-dev --optimize-autoloader
```

## Solución 2: Verificar y cambiar PHP en cPanel para el directorio

1. En cPanel, ir a **"Select PHP Version"**
2. Seleccionar **`services.dowgroupcol.com`** específicamente
3. Asegurarse de que esté en **PHP 8.2**
4. Guardar cambios

## Solución 3: Crear alias o symlink

```bash
# Verificar qué PHP está usando composer
composer --version
composer about

# Crear alias temporal
alias php='/opt/cpanel/ea-php82/root/usr/bin/php'
alias composer='/opt/cpanel/ea-php82/root/usr/bin/php /usr/local/bin/composer'

# Luego ejecutar
composer install --no-dev --optimize-autoloader
```

## Solución 4: Verificar configuración de PHP en el directorio

```bash
cd ~/services.dowgroupcol.com

# Verificar si hay .htaccess que fuerza PHP
cat .htaccess | grep -i php

# Verificar versión que usa el servidor web
php -r "echo PHP_VERSION;"

# Verificar qué PHP usa composer
composer about | grep -i php
```

## Solución 5: Usar php82 directamente

En algunos servidores cPanel, puedes usar comandos específicos:

```bash
php82 /usr/local/bin/composer install --no-dev --optimize-autoloader
# O
php82 $(which composer) install --no-dev --optimize-autoloader
```

## Verificación

Después de aplicar la solución:

```bash
# Verificar versión de PHP
php -v

# Verificar qué PHP usa composer
composer about

# Intentar instalación
composer install --no-dev --optimize-autoloader
```
