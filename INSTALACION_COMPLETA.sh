#!/bin/bash

# Script completo de instalación - Ejecutar paso a paso
# Ejecutar desde: cd ~/services.dowgroupcol.com

echo "🚀 Instalación Completa del CRM"
echo "================================"
echo ""

# Paso 1: Crear tablas faltantes
echo "📋 Paso 1: Creando tablas faltantes..."
chmod +x crear-tablas-faltantes.sh
./crear-tablas-faltantes.sh

# Paso 2: Crear tabla settings si falta
echo ""
echo "📋 Paso 2: Verificando tabla settings..."
chmod +x crear-tabla-settings.sh
./crear-tabla-settings.sh

# Paso 3: Publicar assets de Filament
echo ""
echo "📋 Paso 3: Publicando assets de Filament..."
php artisan filament:assets

# Paso 4: Limpiar caché
echo ""
echo "📋 Paso 4: Limpiando caché..."
php artisan config:clear
php artisan view:clear
php artisan route:clear

echo ""
echo "✅ Instalación completada!"
echo ""
echo "🔐 Accede a: https://services.dowgroupcol.com/admin/login"
echo ""
