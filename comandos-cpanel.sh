#!/bin/bash

# Script de comandos para ejecutar en cPanel después de git pull
# Ejecutar desde: cd ~/services.dowgroupcol.com

echo "🔧 Configurando CRM Services en cPanel..."

# 1. Actualizar dependencias
echo "📦 Actualizando dependencias..."
composer install --no-dev --optimize-autoloader

# 2. Publicar assets de Filament
echo "🎨 Publicando Filament..."
php artisan filament:install --panels

# 3. Instalar/Configurar Shield
echo "🛡️  Configurando Shield..."
composer require bezhanov/filament-shield --no-interaction 2>/dev/null || echo "Shield ya está instalado"
composer dump-autoload

php artisan vendor:publish --tag=filament-shield-config --force 2>/dev/null
php artisan vendor:publish --tag=filament-shield-migrations --force 2>/dev/null

# 4. Ejecutar migraciones pendientes
echo "🗄️  Ejecutando migraciones..."
php artisan migrate --force

# 5. Instalar Shield (si los comandos existen)
if php artisan list | grep -q "shield:install"; then
    echo "🛡️  Instalando Shield..."
    php artisan shield:install --quiet
    php artisan shield:generate --all --quiet
else
    echo "⚠️  Shield no disponible. Ejecuta manualmente: composer require bezhanov/filament-shield"
fi

# 6. Limpiar y optimizar
echo "🧹 Limpiando caché..."
php artisan optimize:clear

echo "⚡ Optimizando..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 7. Permisos
echo "🔐 Configurando permisos..."
chmod -R 755 storage bootstrap/cache public

echo ""
echo "✅ Configuración completada!"
echo "🌐 Accede a: https://services.dowgroupcol.com/admin"
echo ""
