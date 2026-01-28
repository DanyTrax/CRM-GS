#!/bin/bash

# Script para subir cambios al repositorio https://github.com/DanyTrax/CRM-GS
# Ejecutar desde: cd /Users/soporte/Desktop/repos/Services.dow

echo "🔧 Configurando Git para subir a https://github.com/DanyTrax/CRM-GS"
echo ""

# Verificar que estamos en el directorio correcto
if [ ! -f "composer.json" ]; then
    echo "❌ Error: No se encontró composer.json. Asegúrate de estar en el directorio raíz del proyecto."
    exit 1
fi

# Inicializar Git si no está inicializado
if [ ! -d ".git" ]; then
    echo "📦 Inicializando repositorio Git..."
    git init
fi

# Configurar remote
echo "🔗 Configurando remote..."
git remote remove origin 2>/dev/null || true
git remote add origin https://github.com/DanyTrax/CRM-GS.git

# Verificar remote
echo "✅ Remote configurado:"
git remote -v
echo ""

# Verificar si el repositorio remoto tiene contenido
echo "🔍 Verificando repositorio remoto..."
if git ls-remote --heads origin main 2>/dev/null | grep -q "main"; then
    echo "⚠️  El repositorio remoto ya tiene contenido en 'main'"
    echo "   Opción 1: Hacer pull primero (recomendado si quieres mantener el historial)"
    echo "   Opción 2: Forzar push (sobrescribe el contenido remoto)"
    echo ""
    read -p "¿Quieres hacer pull primero? (s/n): " respuesta
    
    if [ "$respuesta" = "s" ] || [ "$respuesta" = "S" ]; then
        echo "📥 Haciendo pull del repositorio remoto..."
        git pull origin main --allow-unrelated-histories || {
            echo "⚠️  Hubo conflictos. Resuélvelos manualmente y luego ejecuta:"
            echo "   git add ."
            echo "   git commit -m 'Resuelto merge'"
            echo "   git push origin main"
            exit 1
        }
    fi
elif git ls-remote --heads origin master 2>/dev/null | grep -q "master"; then
    echo "⚠️  El repositorio remoto tiene contenido en 'master'"
    echo "   Cambiando a rama master..."
    git checkout -b master 2>/dev/null || git checkout master
    git pull origin master --allow-unrelated-histories || true
fi

# Agregar todos los archivos
echo ""
echo "📝 Agregando archivos..."
git add .

# Ver estado
echo ""
echo "📊 Estado de los archivos:"
git status --short | head -20
echo ""

# Hacer commit
echo "💾 Creando commit..."
git commit -m "Migración completa a FilamentPHP v3:
- Removido Shield (no compatible con v3)
- Corregido error de Collision
- Agregado bootstrap/providers.php y bootstrap/app.php para Laravel 11
- Actualizados modelos con nuevos nombres de columnas
- Creados Resources de Filament (Client, Service)
- Instalador visual de 4 pasos
- Scripts de instalación para cPanel
- Documentación completa"

# Determinar rama
CURRENT_BRANCH=$(git branch --show-current 2>/dev/null || echo "master")
if [ "$CURRENT_BRANCH" != "main" ] && [ "$CURRENT_BRANCH" != "master" ]; then
    git branch -M main
    CURRENT_BRANCH="main"
fi

echo ""
echo "🚀 Subiendo cambios a origin/$CURRENT_BRANCH..."
echo "   (Si es la primera vez, puede pedirte autenticación)"
echo ""

# Intentar push
git push -u origin $CURRENT_BRANCH

if [ $? -eq 0 ]; then
    echo ""
    echo "✅ ¡Cambios subidos exitosamente!"
    echo "🌐 Repositorio: https://github.com/DanyTrax/CRM-GS"
else
    echo ""
    echo "⚠️  Error al subir. Posibles causas:"
    echo "   1. No tienes permisos de escritura"
    echo "   2. Necesitas autenticación (Personal Access Token)"
    echo "   3. El repositorio remoto tiene cambios que no tienes localmente"
    echo ""
    echo "💡 Soluciones:"
    echo "   - Verifica tus credenciales de GitHub"
    echo "   - Ejecuta: git pull origin $CURRENT_BRANCH --allow-unrelated-histories"
    echo "   - Luego: git push origin $CURRENT_BRANCH"
fi
