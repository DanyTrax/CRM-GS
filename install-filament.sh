#!/bin/bash

# Script de instalación de Filament y dependencias para cPanel
# Ejecutar desde el directorio raíz del proyecto

echo "🚀 Instalando FilamentPHP y dependencias..."

# 1. Verificar que estamos en el directorio correcto
if [ ! -f "artisan" ]; then
    echo "❌ Error: No se encontró el archivo artisan. Asegúrate de estar en el directorio raíz del proyecto."
    exit 1
fi

# 2. Instalar dependencias de Composer
echo "📦 Instalando dependencias de Composer..."
composer install --no-dev --optimize-autoloader

if [ $? -ne 0 ]; then
    echo "❌ Error al instalar dependencias de Composer"
    exit 1
fi

echo "✅ Dependencias instaladas"

# 3. Publicar assets de Filament
echo "🎨 Publicando assets de Filament..."
php artisan filament:install --panels

if [ $? -ne 0 ]; then
    echo "⚠️  Advertencia: Error al publicar assets de Filament (puede que ya estén publicados)"
fi

# 4. Publicar configuración de Shield (si existe)
echo "🛡️  Configurando Filament Shield..."
php artisan vendor:publish --tag=filament-shield-config --force 2>/dev/null
php artisan vendor:publish --tag=filament-shield-migrations --force 2>/dev/null

# 5. Verificar si Shield está instalado
if php artisan list | grep -q "shield:install"; then
    echo "🛡️  Instalando Filament Shield..."
    php artisan shield:install --quiet
    
    echo "🛡️  Generando roles y permisos..."
    php artisan shield:generate --all --quiet
else
    echo "⚠️  Filament Shield no está disponible. Instálalo manualmente con:"
    echo "   composer require bezhanov/filament-shield"
fi

# 6. Limpiar y optimizar
echo "🧹 Limpiando caché..."
php artisan optimize:clear

echo "⚡ Optimizando..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 7. Configurar permisos
echo "🔐 Configurando permisos..."
chmod -R 755 storage bootstrap/cache
chmod -R 755 public

echo ""
echo "✅ Instalación completada!"
echo ""
echo "📋 Próximos pasos:"
echo "1. Accede a: https://tu-dominio.com/install"
echo "2. Completa el wizard de instalación"
echo "3. Accede al panel admin: https://tu-dominio.com/admin"
echo ""
