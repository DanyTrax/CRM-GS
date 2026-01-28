#!/bin/bash

# Script para publicar assets de Filament y corregir configuración
# Ejecutar desde: cd ~/services.dowgroupcol.com

echo "🎨 Publicando assets de Filament..."

# 1. Asegurar que CACHE_DRIVER esté en 'file'
echo "📝 Configurando CACHE_DRIVER=file..."
if ! grep -q "CACHE_DRIVER=file" .env; then
    if grep -q "CACHE_DRIVER=" .env; then
        sed -i 's/CACHE_DRIVER=.*/CACHE_DRIVER=file/' .env
    else
        echo "CACHE_DRIVER=file" >> .env
    fi
    echo "✅ CACHE_DRIVER configurado a 'file'"
else
    echo "✅ CACHE_DRIVER ya está configurado"
fi

# 2. Publicar assets de Filament
echo "📦 Publicando assets de Filament..."
php artisan filament:assets 2>&1 | grep -v "already exists" || echo "Assets publicados"

# 3. Limpiar caché
echo "🧹 Limpiando caché..."
php artisan config:clear
php artisan view:clear
php artisan route:clear

# 4. Optimizar (opcional, solo si no hay errores)
echo "⚡ Optimizando..."
php artisan config:cache 2>/dev/null || echo "No se pudo optimizar config (normal durante instalación)"

echo ""
echo "✅ Proceso completado!"
echo ""
echo "📋 Verifica:"
echo "1. El login debería verse con estilos correctos"
echo "2. El error de tabla cache debería estar resuelto"
echo ""
