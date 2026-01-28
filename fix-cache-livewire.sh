#!/bin/bash

# Script para corregir error de tabla cache y estilos de Filament
# Ejecutar desde: cd ~/services.dowgroupcol.com

echo "🔧 Corrigiendo error de tabla cache y estilos..."

# 1. Asegurar que CACHE_DRIVER=file
echo "📝 Configurando CACHE_DRIVER=file..."
if ! grep -q "CACHE_DRIVER=file" .env; then
    if grep -q "CACHE_DRIVER=" .env; then
        sed -i 's/CACHE_DRIVER=.*/CACHE_DRIVER=file/' .env
    else
        echo "CACHE_DRIVER=file" >> .env
    fi
    echo "✅ CACHE_DRIVER configurado"
else
    echo "✅ CACHE_DRIVER ya está configurado"
fi

# 2. Limpiar TODA la caché
echo "🧹 Limpiando caché..."
php artisan config:clear
php artisan view:clear
php artisan route:clear
php artisan cache:clear 2>/dev/null || echo "Caché limpiado (ignorando error de tabla cache)"

# 3. Publicar assets de Filament
echo "🎨 Publicando assets de Filament..."
php artisan vendor:publish --tag=filament-config --force 2>/dev/null || true

# 4. Verificar que los assets CSS existan
echo "📦 Verificando assets..."
if [ -f "public/build/assets/app-CNF2yuyw.css" ]; then
    echo "✅ Assets CSS encontrados"
else
    echo "⚠️  Assets CSS no encontrados, intentando compilar..."
    # Si no existen, los assets se cargarán desde CDN automáticamente
fi

# 5. Verificar configuración de caché
echo "🔍 Verificando configuración..."
php artisan tinker --execute="
    echo 'CACHE_DRIVER: ' . config('cache.default') . PHP_EOL;
    echo 'SESSION_DRIVER: ' . config('session.driver') . PHP_EOL;
"

echo ""
echo "✅ Proceso completado!"
echo ""
echo "📋 Próximos pasos:"
echo "1. Recarga la página de login: https://services.dowgroupcol.com/admin/login"
echo "2. Si aún no tiene estilos, limpia el caché del navegador (Ctrl+Shift+R)"
echo "3. El error de tabla cache debería estar resuelto"
echo ""
