#!/bin/bash

# Script de comandos para FilamentPHP v3 (SIN Shield, SIN Collision)
# Ejecutar desde: cd ~/services.dowgroupcol.com

echo "🔧 Configurando CRM Services con FilamentPHP v3..."

# 1. Actualizar dependencias (sin dev dependencies en producción)
echo "📦 Instalando dependencias..."
composer install --no-dev --optimize-autoloader

if [ $? -ne 0 ]; then
    echo "❌ Error al instalar dependencias"
    exit 1
fi

# 2. Publicar assets de Filament
echo "🎨 Publicando Filament..."
php artisan filament:install --panels 2>/dev/null || echo "Filament ya está instalado"

# 3. Publicar Spatie Permission (opcional)
echo "🔐 Configurando Spatie Permission..."
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider" 2>/dev/null || echo "Spatie Permission ya publicado"

# 4. Ejecutar migraciones pendientes
echo "🗄️  Ejecutando migraciones..."
php artisan migrate --force

# 5. Limpiar caché (SIN usar comandos que requieren Collision)
echo "🧹 Limpiando caché..."
php artisan cache:clear 2>/dev/null || true
php artisan config:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true

# NO usar optimize:clear ni config:cache en producción si Collision no está instalado
# En su lugar, solo limpiar manualmente
echo "📝 Nota: Si necesitas optimizar, ejecuta manualmente después de verificar que todo funciona"

# 6. Permisos
echo "🔐 Configurando permisos..."
chmod -R 755 storage bootstrap/cache public 2>/dev/null || true

echo ""
echo "✅ Configuración completada!"
echo ""
echo "📋 Nota:"
echo "   - Filament Shield NO está disponible para v3"
echo "   - Collision es solo para desarrollo, no se instala en producción"
echo "   - Los comandos de caché pueden fallar si Collision no está instalado"
echo ""
echo "🌐 Accede a: https://services.dowgroupcol.com/admin"
echo ""
