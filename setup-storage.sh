#!/bin/bash

# Script para crear estructura de directorios necesarios
# Ejecutar desde: cd ~/services.dowgroupcol.com

echo "📁 Creando estructura de directorios..."

# Crear directorios de storage
mkdir -p storage/app/backups
mkdir -p storage/app/public
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs

# Crear directorios de bootstrap
mkdir -p bootstrap/cache

# Configurar permisos
chmod -R 755 storage bootstrap/cache
chmod -R 755 public

echo "✅ Estructura de directorios creada"
echo ""
