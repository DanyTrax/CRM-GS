#!/bin/bash

# Script de instalación automática
# Este script se ejecuta automáticamente si Composer no está instalado

echo "🚀 Iniciando instalación automática del CRM..."

# Verificar si Composer está instalado
if ! command -v composer &> /dev/null; then
    echo "❌ Composer no está instalado."
    echo "Por favor, instala Composer primero: https://getcomposer.org/download/"
    exit 1
fi

# Verificar si existe vendor
if [ ! -d "vendor" ]; then
    echo "📦 Instalando dependencias de Composer..."
    composer install --no-interaction --prefer-dist --optimize-autoloader
    
    if [ $? -ne 0 ]; then
        echo "❌ Error al instalar dependencias"
        exit 1
    fi
    echo "✅ Dependencias instaladas"
else
    echo "✅ Dependencias ya instaladas"
fi

# Verificar si existe .env
if [ ! -f ".env" ]; then
    echo "📝 Creando archivo .env..."
    if [ -f ".env.example" ]; then
        cp .env.example .env
        echo "✅ Archivo .env creado"
    else
        echo "⚠️  No se encontró .env.example"
    fi
else
    echo "✅ Archivo .env ya existe"
fi

# Crear directorios necesarios
echo "📁 Creando directorios necesarios..."
mkdir -p storage/app/backups
mkdir -p storage/app/public
mkdir -p storage/logs
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
chmod -R 775 storage bootstrap/cache
echo "✅ Directorios creados"

echo ""
echo "✅ Instalación automática completada!"
echo ""
echo "📋 Próximos pasos:"
echo "1. Accede a: http://tu-dominio/install"
echo "2. Completa el wizard de instalación"
echo "3. ¡Listo! Tu sistema estará funcionando"
echo ""
