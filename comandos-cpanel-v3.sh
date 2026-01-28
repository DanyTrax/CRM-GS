#!/bin/bash

# Script de comandos para FilamentPHP v3 (SIN Shield, SIN Collision)
# Ejecutar desde: cd ~/services.dowgroupcol.com

echo "🔧 Configurando CRM Services con FilamentPHP v3..."

# 0. Crear estructura de directorios
echo "📁 Creando estructura de directorios..."
mkdir -p storage/app/backups storage/app/public storage/framework/cache storage/framework/sessions storage/framework/views storage/logs
mkdir -p bootstrap/cache
chmod -R 755 storage bootstrap/cache public 2>/dev/null || true

# 1. Actualizar dependencias (actualizar lock file si es necesario)
echo "📦 Actualizando dependencias..."
echo "   (Si el lock file está desactualizado, se actualizará automáticamente)"

# Primero intentar install, si falla, hacer update
if ! composer install --no-dev --optimize-autoloader 2>&1 | grep -q "lock file is not up to date"; then
    composer install --no-dev --optimize-autoloader
else
    echo "⚠️  Lock file desactualizado. Actualizando..."
    composer update --no-dev --optimize-autoloader --with-all-dependencies
fi

if [ $? -ne 0 ]; then
    echo "❌ Error al instalar dependencias"
    echo "💡 Intentando actualizar lock file..."
    composer update --no-dev --optimize-autoloader --with-all-dependencies
    
    if [ $? -ne 0 ]; then
        echo "❌ Error crítico. Verifica los logs arriba."
        exit 1
    fi
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
