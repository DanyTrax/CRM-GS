#!/bin/bash

# Script de diagnóstico para error 500 en /install

echo "=== Diagnóstico de Instalación CRM-GS ==="
echo ""

cd ~/services.dowgroupcol.com || cd "$(dirname "$0")"

echo "1. Verificando archivo .env..."
if [ -f .env ]; then
    echo "   ✓ .env existe"
    if grep -q "APP_KEY=" .env && ! grep -q "APP_KEY=$" .env; then
        echo "   ✓ APP_KEY está configurado"
    else
        echo "   ✗ APP_KEY no está configurado"
        echo "   Ejecutar: php artisan key:generate"
    fi
else
    echo "   ✗ .env NO existe"
    echo "   Ejecutar: cp .env.example .env && php artisan key:generate"
fi

echo ""
echo "2. Verificando estructura de storage..."
if [ -d "storage" ]; then
    echo "   ✓ Carpeta storage existe"
    [ -d "storage/logs" ] && echo "   ✓ storage/logs existe" || echo "   ✗ storage/logs NO existe"
    [ -d "storage/framework" ] && echo "   ✓ storage/framework existe" || echo "   ✗ storage/framework NO existe"
    [ -d "storage/app" ] && echo "   ✓ storage/app existe" || echo "   ✗ storage/app NO existe"
else
    echo "   ✗ Carpeta storage NO existe"
    echo "   Ejecutar: bash setup-storage.sh"
fi

echo ""
echo "3. Verificando permisos de storage..."
if [ -d "storage" ]; then
    PERMS=$(stat -c "%a" storage 2>/dev/null || stat -f "%OLp" storage 2>/dev/null)
    echo "   Permisos de storage: $PERMS"
    if [ "$PERMS" = "775" ] || [ "$PERMS" = "755" ] || [ "$PERMS" = "777" ]; then
        echo "   ✓ Permisos correctos"
    else
        echo "   ✗ Permisos incorrectos (debe ser 775 o 755)"
        echo "   Ejecutar: chmod -R 775 storage"
    fi
fi

echo ""
echo "4. Verificando vendor (dependencias)..."
if [ -d "vendor" ]; then
    echo "   ✓ vendor existe"
    if [ -f "vendor/autoload.php" ]; then
        echo "   ✓ autoload.php existe"
    else
        echo "   ✗ autoload.php NO existe"
        echo "   Ejecutar: composer install"
    fi
else
    echo "   ✗ vendor NO existe"
    echo "   Ejecutar: composer install --no-dev --optimize-autoloader"
fi

echo ""
echo "5. Verificando index.php..."
if [ -f "index.php" ]; then
    echo "   ✓ index.php existe"
    if grep -q "__DIR__.'/storage" index.php; then
        echo "   ✓ Rutas en index.php parecen correctas"
    else
        echo "   ⚠ Verificar rutas en index.php"
    fi
else
    echo "   ✗ index.php NO existe en la raíz"
fi

echo ""
echo "6. Verificando logs de error..."
if [ -f "storage/logs/laravel.log" ]; then
    echo "   Últimas líneas del log:"
    tail -10 storage/logs/laravel.log | sed 's/^/   /'
else
    echo "   ⚠ No hay archivo de log aún"
fi

echo ""
echo "=== Fin del diagnóstico ==="
echo ""
echo "Si hay errores, seguir las instrucciones mostradas arriba."
