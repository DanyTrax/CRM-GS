#!/bin/bash

# Script para crear la estructura de carpetas de storage
# Ejecutar después de clonar el repositorio

echo "Creando estructura de carpetas de storage..."

# Crear carpetas principales
mkdir -p storage/app/public
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
mkdir -p storage/app/backups
mkdir -p bootstrap/cache

# Crear archivos .gitkeep para mantener estructura en Git
touch storage/app/.gitkeep
touch storage/app/public/.gitkeep
touch storage/framework/cache/.gitkeep
touch storage/framework/sessions/.gitkeep
touch storage/framework/views/.gitkeep
touch storage/logs/.gitkeep
touch storage/app/backups/.gitkeep
touch bootstrap/cache/.gitkeep

# Configurar permisos
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Asegurar que bootstrap/cache sea escribible
chmod 777 bootstrap/cache 2>/dev/null || chmod 775 bootstrap/cache

echo "✓ Estructura de storage creada exitosamente"
echo "✓ Permisos configurados"
