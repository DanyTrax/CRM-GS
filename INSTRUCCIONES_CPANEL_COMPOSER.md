# 🔧 Instrucciones para Corregir Error de Composer en cPanel

## ❌ Problema

El error muestra que `composer.json` en el servidor todavía tiene:
```json
"owen-it/laravel-auditing": "^15.0"
```

Pero la versión correcta (ya subida al repositorio) es:
```json
"owen-it/laravel-auditing": "^14.0"
```

## ✅ Solución Rápida (Recomendada)

Ejecuta este script que fuerza la actualización:

```bash
cd ~/services.dowgroupcol.com

# 1. Descargar el script actualizado
git pull

# 2. Ejecutar script de corrección
chmod +x fix-composer.sh
./fix-composer.sh
```

Este script:
- ✅ Fuerza la actualización desde Git
- ✅ Corrige automáticamente la versión en `composer.json`
- ✅ Elimina `composer.lock` antiguo
- ✅ Actualiza todas las dependencias

## 🔄 Solución Manual (Si el script no funciona)

```bash
cd ~/services.dowgroupcol.com

# 1. Forzar actualización desde repositorio
git fetch origin main
git reset --hard origin/main

# 2. Verificar que composer.json tenga la versión correcta
grep "laravel-auditing" composer.json
# Debe mostrar: "owen-it/laravel-auditing": "^14.0"

# 3. Si todavía muestra ^15.0, corregir manualmente:
sed -i 's/"owen-it\/laravel-auditing": "\^15.0"/"owen-it\/laravel-auditing": "^14.0"/g' composer.json

# 4. Eliminar composer.lock antiguo
rm -f composer.lock

# 5. Actualizar dependencias
composer update --no-dev --optimize-autoloader --with-all-dependencies
```

## 📋 Verificación

Después de ejecutar los comandos, verifica:

```bash
# 1. Verificar versión en composer.json
grep "laravel-auditing" composer.json

# 2. Verificar que composer funciona
composer show owen-it/laravel-auditing

# 3. Verificar que todas las dependencias están instaladas
composer install --no-dev --optimize-autoloader
```

## 🚨 Si Sigue Fallando

Si después de todo esto sigue fallando, puede ser un problema de caché de Composer:

```bash
# Limpiar caché de Composer
composer clear-cache

# Intentar de nuevo
composer update --no-dev --optimize-autoloader --with-all-dependencies
```

---

**Nota:** El repositorio en GitHub ya tiene la versión correcta (`^14.0`). El problema es que el servidor necesita actualizar el código desde Git.
