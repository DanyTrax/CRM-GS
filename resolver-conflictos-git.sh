#!/bin/bash

echo "🔧 Resolviendo conflictos de Git para permitir git pull..."

# 1. Eliminar archivo de caché modificado (debe estar en .gitignore)
echo "1. Eliminando bootstrap/cache/services.php (archivo de caché)..."
rm -f bootstrap/cache/services.php
echo "   ✅ Archivo de caché eliminado"

# 2. Hacer stash de cualquier cambio local
echo "2. Haciendo stash de cambios locales..."
git stash push -m "Stash automático antes de pull - $(date)" 2>/dev/null || echo "   ℹ️  No hay cambios para hacer stash"
echo "   ✅ Stash completado"

# 3. Eliminar archivos de assets de Filament si existen localmente
# (se regenerarán con php artisan filament:assets)
echo "3. Eliminando assets de Filament locales (se regenerarán)..."
rm -rf public/css/filament
rm -rf public/js/filament
echo "   ✅ Assets eliminados"

# 4. Ahora hacer pull
echo "4. Ejecutando git pull..."
git pull origin main

if [ $? -eq 0 ]; then
    echo ""
    echo "✅ git pull completado exitosamente"
    echo ""
    echo "📋 Próximos pasos:"
    echo "   1. Regenerar assets de Filament: php artisan filament:assets"
    echo "   2. Limpiar caché: php artisan optimize:clear"
else
    echo ""
    echo "❌ Error al hacer git pull"
    echo "   Revisa los mensajes de error arriba"
    exit 1
fi
