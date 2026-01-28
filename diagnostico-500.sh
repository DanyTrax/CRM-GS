#!/bin/bash

# Script de diagnóstico para error 500

echo "=== Diagnóstico de Error 500 ==="
echo ""

cd ~/services.dowgroupcol.com || cd "$(dirname "$0")"

echo "1. Verificando archivos básicos..."
[ -f index.php ] && echo "   ✓ index.php existe" || echo "   ✗ index.php NO existe"
[ -f .htaccess ] && echo "   ✓ .htaccess existe" || echo "   ✗ .htaccess NO existe"
[ -f .env ] && echo "   ✓ .env existe" || echo "   ✗ .env NO existe"
[ -f app/Http/Controllers/Controller.php ] && echo "   ✓ Controller.php existe" || echo "   ✗ Controller.php NO existe"

echo ""
echo "2. Verificando estructura de storage..."
[ -d storage/logs ] && echo "   ✓ storage/logs existe" || echo "   ✗ storage/logs NO existe"
[ -d storage/framework/cache ] && echo "   ✓ storage/framework/cache existe" || echo "   ✗ storage/framework/cache NO existe"
[ -d bootstrap/cache ] && echo "   ✓ bootstrap/cache existe" || echo "   ✗ bootstrap/cache NO existe"

echo ""
echo "3. Verificando permisos..."
STORAGE_PERMS=$(stat -c "%a" storage 2>/dev/null || stat -f "%OLp" storage 2>/dev/null)
BOOTSTRAP_PERMS=$(stat -c "%a" bootstrap/cache 2>/dev/null || stat -f "%OLp" bootstrap/cache 2>/dev/null)
echo "   Permisos de storage: $STORAGE_PERMS"
echo "   Permisos de bootstrap/cache: $BOOTSTRAP_PERMS"

echo ""
echo "4. Verificando APP_KEY..."
if grep -q "APP_KEY=base64:" .env 2>/dev/null; then
    echo "   ✓ APP_KEY está configurado"
else
    echo "   ✗ APP_KEY NO está configurado"
    echo "   Ejecutar: php artisan key:generate"
fi

echo ""
echo "5. Verificando base de datos..."
if grep -q "DB_DATABASE=" .env 2>/dev/null && ! grep -q "DB_DATABASE=$" .env 2>/dev/null; then
    echo "   ✓ Base de datos configurada en .env"
else
    echo "   ✗ Base de datos NO configurada"
fi

echo ""
echo "6. Verificando vendor..."
[ -d vendor ] && echo "   ✓ vendor existe" || echo "   ✗ vendor NO existe (ejecutar: composer install)"

echo ""
echo "7. Verificando rutas..."
php artisan route:list 2>&1 | head -3 > /dev/null
if [ $? -eq 0 ]; then
    echo "   ✓ Las rutas se cargan correctamente"
else
    echo "   ✗ Error al cargar rutas"
fi

echo ""
echo "8. Últimos errores en el log (últimas 30 líneas):"
echo "   ==========================================="
if [ -f storage/logs/laravel.log ]; then
    tail -30 storage/logs/laravel.log | sed 's/^/   /'
else
    echo "   No hay archivo de log aún"
fi

echo ""
echo "=== Fin del diagnóstico ==="
echo ""
echo "Revisa especialmente la sección 8 (logs) para ver el error específico."
