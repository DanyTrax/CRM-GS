#!/bin/bash

# Script para forzar actualización de composer.json y resolver conflictos
# Ejecutar desde: cd ~/services.dowgroupcol.com

echo "🔧 Forzando actualización de composer.json..."

# 1. Asegurar que estamos en la rama correcta
git checkout main 2>/dev/null || true

# 2. Descartar cambios locales en composer.json (si los hay)
echo "📝 Descartando cambios locales en composer.json..."
git checkout -- composer.json 2>/dev/null || true

# 3. Forzar pull del repositorio
echo "⬇️  Actualizando desde repositorio..."
git fetch origin main
git reset --hard origin/main

# 4. Verificar versión de laravel-auditing
echo ""
echo "✅ Verificando composer.json..."
if grep -q '"owen-it/laravel-auditing": "^14.0"' composer.json; then
    echo "   ✓ Versión correcta: ^14.0"
else
    echo "   ✗ Versión incorrecta. Corrigiendo..."
    # Forzar corrección manual
    sed -i 's/"owen-it\/laravel-auditing": "\^15.0"/"owen-it\/laravel-auditing": "^14.0"/g' composer.json
    sed -i 's/"owen-it\/laravel-auditing": "\^15"/"owen-it\/laravel-auditing": "^14.0"/g' composer.json
    echo "   ✓ Corregido manualmente"
fi

# 5. Eliminar composer.lock antiguo si existe
if [ -f composer.lock ]; then
    echo "🗑️  Eliminando composer.lock antiguo..."
    rm composer.lock
fi

# 6. Actualizar dependencias
echo ""
echo "📦 Actualizando dependencias de Composer..."
composer update --no-dev --optimize-autoloader --with-all-dependencies

if [ $? -eq 0 ]; then
    echo ""
    echo "✅ Composer actualizado correctamente!"
else
    echo ""
    echo "❌ Error al actualizar. Intentando install..."
    composer install --no-dev --optimize-autoloader
fi

echo ""
echo "📋 Estado final:"
composer show owen-it/laravel-auditing 2>/dev/null || echo "   (No instalado aún)"
echo ""
