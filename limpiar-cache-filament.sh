#!/bin/bash

# Script para limpiar caché de Filament y Laravel
# Ejecutar desde: cd ~/services.dowgroupcol.com

echo "🧹 Limpiando caché de Filament y Laravel..."

# Limpiar todas las cachés
php artisan config:clear
php artisan view:clear
php artisan route:clear
php artisan cache:clear 2>/dev/null || echo "Cache clear ignorado (tabla cache puede no existir)"

# Limpiar caché de componentes de Filament
php artisan filament:clear-cached-components 2>/dev/null || echo "Filament components cache no disponible"

# Limpiar caché de optimización de Filament
php artisan filament:optimize-clear 2>/dev/null || echo "Filament optimize clear no disponible"

# Verificar rutas de Filament
echo ""
echo "🔍 Verificando rutas de Filament..."
php artisan route:list | grep filament | head -10

echo ""
echo "✅ Caché limpiado!"
echo ""
echo "📋 Próximos pasos:"
echo "1. Recarga la página del admin: https://services.dowgroupcol.com/admin"
echo "2. Si aún hay errores, verifica que los Resources existan en app/Filament/Resources/"
echo ""
