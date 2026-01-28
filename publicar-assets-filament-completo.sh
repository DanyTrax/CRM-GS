#!/bin/bash

# Script para publicar TODOS los assets de Filament
# Ejecutar desde: cd ~/services.dowgroupcol.com

echo "🎨 Publicando assets de Filament..."

# 1. Limpiar caché
echo "🧹 Limpiando caché..."
php artisan config:clear
php artisan view:clear
php artisan route:clear

# 2. Publicar assets de Filament usando el comando específico
echo "📦 Publicando assets de Filament..."
php artisan filament:assets 2>&1

# 3. Si el comando anterior no funciona, intentar publicar manualmente
if [ ! -d "public/css/filament" ] || [ ! -d "public/js/filament" ]; then
    echo "⚠️  Assets no encontrados, intentando publicación manual..."
    
    # Publicar todos los assets de Filament
    php artisan vendor:publish --tag=filament-assets --force 2>&1
    
    # Publicar configuración de Filament
    php artisan vendor:publish --tag=filament-config --force 2>&1
    
    # Publicar todos los assets de los paquetes de Filament
    php artisan vendor:publish --provider="Filament\FilamentServiceProvider" --force 2>&1
    php artisan vendor:publish --provider="Filament\Forms\FormsServiceProvider" --force 2>&1
    php artisan vendor:publish --provider="Filament\Support\SupportServiceProvider" --force 2>&1
    php artisan vendor:publish --provider="Filament\Notifications\NotificationsServiceProvider" --force 2>&1
fi

# 4. Verificar que los assets existan
echo ""
echo "🔍 Verificando assets publicados..."
if [ -d "public/css/filament" ]; then
    echo "✅ CSS assets encontrados:"
    ls -la public/css/filament/*/ 2>/dev/null | head -5 || echo "   (directorios vacíos o no encontrados)"
else
    echo "❌ Directorio public/css/filament no existe"
fi

if [ -d "public/js/filament" ]; then
    echo "✅ JS assets encontrados:"
    ls -la public/js/filament/*/ 2>/dev/null | head -5 || echo "   (directorios vacíos o no encontrados)"
else
    echo "❌ Directorio public/js/filament no existe"
fi

# 5. Verificar permisos
echo ""
echo "🔐 Verificando permisos..."
chmod -R 755 public/css 2>/dev/null || true
chmod -R 755 public/js 2>/dev/null || true

# 6. Limpiar caché nuevamente
echo ""
echo "🧹 Limpiando caché nuevamente..."
php artisan config:clear
php artisan view:clear

echo ""
echo "✅ Proceso completado!"
echo ""
echo "📋 Verificación:"
echo "1. Los assets deberían estar en:"
echo "   - public/css/filament/"
echo "   - public/js/filament/"
echo ""
echo "2. Recarga la página de login con Ctrl+Shift+R (limpiar caché del navegador)"
echo "3. Los estilos deberían cargarse correctamente"
echo ""
