#!/bin/bash

# Script para verificar y corregir configuración de base de datos
# Ejecutar desde: cd ~/services.dowgroupcol.com

echo "🔍 Verificando configuración de base de datos..."

# 1. Verificar .env
echo "📝 Verificando .env..."
if [ -f .env ]; then
    echo "DB_DATABASE actual:"
    grep "^DB_DATABASE=" .env || echo "DB_DATABASE no encontrado"
    
    echo ""
    echo "DB_HOST actual:"
    grep "^DB_HOST=" .env || echo "DB_HOST no encontrado"
    
    echo ""
    echo "DB_USERNAME actual:"
    grep "^DB_USERNAME=" .env || echo "DB_USERNAME no encontrado"
    
    echo ""
    echo "CACHE_DRIVER actual:"
    grep "^CACHE_DRIVER=" .env || echo "CACHE_DRIVER no encontrado (debería ser 'file')"
else
    echo "❌ Archivo .env no encontrado"
    exit 1
fi

# 2. Verificar que DB_DATABASE no tenga espacios o caracteres especiales
echo ""
echo "🔧 Verificando formato de DB_DATABASE..."
DB_NAME=$(grep "^DB_DATABASE=" .env | cut -d'=' -f2 | tr -d '"' | tr -d "'" | xargs)

if [[ "$DB_NAME" =~ [[:space:]] ]]; then
    echo "⚠️  ADVERTENCIA: DB_DATABASE contiene espacios: '$DB_NAME'"
    echo "   Esto puede causar problemas. El nombre debería ser: 'dowgroupcol_serv.dow'"
    echo ""
    echo "   ¿Quieres corregirlo? (s/n)"
    read -r respuesta
    if [ "$respuesta" = "s" ]; then
        # Extraer solo el nombre correcto (antes del punto si hay espacios)
        CORRECT_DB_NAME="dowgroupcol_serv.dow"
        sed -i "s/^DB_DATABASE=.*/DB_DATABASE=$CORRECT_DB_NAME/" .env
        echo "✅ DB_DATABASE corregido a: $CORRECT_DB_NAME"
    fi
else
    echo "✅ DB_DATABASE tiene formato correcto: '$DB_NAME'"
fi

# 3. Asegurar que CACHE_DRIVER=file
echo ""
echo "🔧 Verificando CACHE_DRIVER..."
if ! grep -q "^CACHE_DRIVER=file" .env; then
    if grep -q "^CACHE_DRIVER=" .env; then
        sed -i 's/^CACHE_DRIVER=.*/CACHE_DRIVER=file/' .env
        echo "✅ CACHE_DRIVER cambiado a 'file'"
    else
        echo "CACHE_DRIVER=file" >> .env
        echo "✅ CACHE_DRIVER agregado como 'file'"
    fi
else
    echo "✅ CACHE_DRIVER ya está configurado como 'file'"
fi

# 4. Limpiar caché
echo ""
echo "🧹 Limpiando caché..."
php artisan config:clear
php artisan view:clear
php artisan route:clear

# 5. Verificar conexión
echo ""
echo "🔌 Verificando conexión a base de datos..."
php artisan tinker --execute="
    try {
        \$dbName = config('database.connections.mysql.database');
        echo 'Nombre de BD configurado: ' . \$dbName . PHP_EOL;
        
        DB::connection('mysql')->getPdo();
        echo '✅ Conexión exitosa' . PHP_EOL;
        
        // Verificar que la tabla users existe
        if (DB::getSchemaBuilder()->hasTable('users')) {
            echo '✅ Tabla users existe' . PHP_EOL;
        } else {
            echo '⚠️  Tabla users NO existe' . PHP_EOL;
        }
    } catch (\Exception \$e) {
        echo '❌ Error de conexión: ' . \$e->getMessage() . PHP_EOL;
    }
"

echo ""
echo "✅ Verificación completada!"
echo ""
