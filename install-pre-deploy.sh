#!/bin/bash

# Script de instalación previa al despliegue
# Ejecutar DESPUÉS de clonar y ANTES de acceder al sistema

echo "=== Instalación Previa al Despliegue CRM-GS ==="
echo ""

cd ~/services.dowgroupcol.com || cd "$(dirname "$0")"

# Verificar que estamos en el directorio correcto
if [ ! -f "composer.json" ]; then
    echo "Error: No se encontró composer.json"
    echo "Asegúrate de estar en el directorio del proyecto"
    exit 1
fi

echo "1. Instalando dependencias de Composer..."
if [ -d "vendor" ]; then
    echo "   ⚠ vendor ya existe, omitiendo..."
else
    composer install --no-dev --optimize-autoloader
    if [ $? -eq 0 ]; then
        echo "   ✓ Composer install completado"
    else
        echo "   ✗ Error en composer install"
        exit 1
    fi
fi

echo ""
echo "2. Instalando dependencias de Node.js..."
if [ -d "node_modules" ]; then
    echo "   ⚠ node_modules ya existe, omitiendo..."
else
    npm install
    if [ $? -eq 0 ]; then
        echo "   ✓ npm install completado"
    else
        echo "   ✗ Error en npm install"
        exit 1
    fi
fi

echo ""
echo "3. Configurando archivo .env..."
if [ -f ".env" ]; then
    echo "   ⚠ .env ya existe"
    read -p "   ¿Deseas regenerar APP_KEY? (s/n): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Ss]$ ]]; then
        php artisan key:generate
        echo "   ✓ APP_KEY regenerado"
    else
        echo "   → Manteniendo .env existente"
    fi
else
    if [ -f ".env.example" ]; then
        cp .env.example .env
        php artisan key:generate
        echo "   ✓ .env creado y APP_KEY generado"
    else
        echo "   ✗ No se encontró .env.example"
        exit 1
    fi
fi

echo ""
echo "4. Publicando migraciones de Spatie Permission..."
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider" --tag="migrations"
if [ $? -eq 0 ]; then
    echo "   ✓ Migraciones de Spatie publicadas"
else
    echo "   ✗ Error al publicar migraciones de Spatie"
    echo "   Verificar que spatie/laravel-permission esté instalado"
    exit 1
fi

echo ""
echo "5. Verificando estructura de storage..."
if [ ! -d "storage" ]; then
    echo "   ⚠ storage no existe, ejecutando setup-storage.sh..."
    bash setup-storage.sh
else
    echo "   ✓ storage existe"
fi

echo ""
echo "=== Instalación Previa Completada ==="
echo ""
echo "PRÓXIMOS PASOS:"
echo "1. Editar .env y configurar base de datos:"
echo "   nano .env"
echo "   (Configurar DB_DATABASE, DB_USERNAME, DB_PASSWORD)"
echo ""
echo "2. Crear base de datos desde cPanel"
echo ""
echo "3. Ejecutar migraciones:"
echo "   php artisan migrate --seed"
echo ""
echo "4. Compilar assets:"
echo "   npm run build"
echo ""
echo "5. Acceder al sistema:"
echo "   https://services.dowgroupcol.com/admin"
echo ""
