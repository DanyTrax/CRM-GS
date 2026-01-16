# Actualizar PHP a 8.2+ en cPanel

## Problema
El servidor tiene PHP 8.1.33 pero el proyecto requiere PHP 8.2 o superior.

## Solución: Actualizar PHP en cPanel

### Opción 1: Desde cPanel (Recomendado)

1. **Acceder a cPanel**
2. **Buscar "Select PHP Version"** o "MultiPHP Manager"
3. **Seleccionar el dominio/subdominio** `services.dowgroupcol.com`
4. **Cambiar la versión de PHP** a **8.2** o superior (8.3, 8.4)
5. **Guardar cambios**

### Opción 2: Desde SSH

```bash
# Verificar versión actual
php -v

# Si tienes acceso root o sudo
# Actualizar PHP según tu distribución
```

### Opción 3: Usar PHP Selector de cPanel

1. En cPanel, ir a **"Select PHP Version"**
2. Seleccionar **PHP 8.2** o superior
3. Asegurarse de que estas extensiones estén habilitadas:
   - ✅ BCMath
   - ✅ Ctype
   - ✅ JSON
   - ✅ Mbstring
   - ✅ OpenSSL
   - ✅ PDO
   - ✅ XML
   - ✅ Tokenizer
   - ✅ Fileinfo

### Verificar después de actualizar

```bash
php -v
# Debe mostrar PHP 8.2.x o superior

cd ~/services.dowgroupcol.com
composer install --no-dev --optimize-autoloader
```

## Si no puedes actualizar PHP

Si por alguna razón no puedes actualizar PHP a 8.2+, puedes ajustar temporalmente `composer.json` para aceptar PHP 8.1, **PERO esto puede causar problemas** porque Laravel 11 requiere PHP 8.2.

**No recomendado**, pero si es necesario:

```json
"require": {
    "php": "^8.1",
    ...
}
```

Esto puede causar errores en tiempo de ejecución.
