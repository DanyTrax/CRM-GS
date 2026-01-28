#!/bin/bash

echo "🧹 Limpiando caché completo de Laravel y Filament..."

# 1. Limpiar caché de bootstrap (CRÍTICO)
echo "1. Limpiando caché de bootstrap..."
rm -rf bootstrap/cache/*.php
echo "   ✅ Caché de bootstrap limpiado"

# 2. Limpiar todos los cachés de Laravel
echo "2. Limpiando cachés de Laravel..."
php artisan route:clear
php artisan config:clear
php artisan view:clear
php artisan cache:clear
php artisan event:clear
php artisan optimize:clear
echo "   ✅ Cachés de Laravel limpiados"

# 3. Regenerar autoload de Composer
echo "3. Regenerando autoload de Composer..."
composer dump-autoload --optimize
echo "   ✅ Autoload regenerado"

# 4. Publicar assets de Filament (por si acaso)
echo "4. Publicando assets de Filament..."
php artisan filament:assets 2>/dev/null || echo "   ⚠️  Assets ya publicados o comando no disponible"
echo "   ✅ Assets verificados"

# 5. Verificar rutas de Filament
echo "5. Verificando rutas de Filament..."
echo ""
echo "   Rutas de Tickets:"
php artisan route:list --name=filament.admin.resources.tickets 2>/dev/null | grep -E "(tickets|filament)" || echo "   ⚠️  No se encontraron rutas de tickets"
echo ""
echo "   Rutas de Clientes:"
php artisan route:list --name=filament.admin.resources.clients 2>/dev/null | grep -E "(clients|filament)" || echo "   ⚠️  No se encontraron rutas de clientes"
echo ""

# 6. Ejecutar diagnóstico
echo "6. Ejecutando diagnóstico de Filament..."
php artisan filament:diagnose 2>&1 | tail -20
echo ""

echo "✅ Limpieza completa finalizada"
echo ""
echo "📋 Próximos pasos:"
echo "   1. Recarga la página /admin en tu navegador"
echo "   2. Si aún hay errores, revisa el log en storage/logs/filament-diagnosis-*.log"
echo "   3. Verifica los permisos de storage/ y bootstrap/cache/"
