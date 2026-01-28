#!/bin/bash

# Script para agregar columnas faltantes a la tabla users
# Ejecutar desde: cd ~/services.dowgroupcol.com

echo "🔧 Verificando y corrigiendo estructura de tabla users..."

php artisan tinker --execute="
    // Verificar columnas existentes
    \$columns = DB::select('SHOW COLUMNS FROM users');
    \$columnNames = array_column(\$columns, 'Field');
    
    echo 'Columnas actuales: ' . implode(', ', \$columnNames) . PHP_EOL;
    
    // Agregar columnas faltantes
    if (!in_array('role_id', \$columnNames)) {
        echo 'Agregando role_id...' . PHP_EOL;
        DB::statement('ALTER TABLE users ADD COLUMN role_id BIGINT UNSIGNED NULL AFTER password');
        DB::statement('ALTER TABLE users ADD CONSTRAINT users_role_id_foreign FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE SET NULL');
    }
    
    if (!in_array('two_factor_secret', \$columnNames)) {
        echo 'Agregando two_factor_secret...' . PHP_EOL;
        DB::statement('ALTER TABLE users ADD COLUMN two_factor_secret TEXT NULL AFTER role_id');
    }
    
    if (!in_array('two_factor_recovery_codes', \$columnNames)) {
        echo 'Agregando two_factor_recovery_codes...' . PHP_EOL;
        DB::statement('ALTER TABLE users ADD COLUMN two_factor_recovery_codes TEXT NULL AFTER two_factor_secret');
    }
    
    if (!in_array('avatar', \$columnNames)) {
        echo 'Agregando avatar...' . PHP_EOL;
        DB::statement('ALTER TABLE users ADD COLUMN avatar VARCHAR(255) NULL AFTER two_factor_recovery_codes');
    }
    
    // Eliminar columnas antiguas si existen
    if (in_array('google2fa_secret', \$columnNames)) {
        echo 'Eliminando google2fa_secret...' . PHP_EOL;
        DB::statement('ALTER TABLE users DROP COLUMN google2fa_secret');
    }
    
    if (in_array('google2fa_enabled', \$columnNames)) {
        echo 'Eliminando google2fa_enabled...' . PHP_EOL;
        DB::statement('ALTER TABLE users DROP COLUMN google2fa_enabled');
    }
    
    if (in_array('two_factor_enabled', \$columnNames)) {
        echo 'Eliminando two_factor_enabled...' . PHP_EOL;
        DB::statement('ALTER TABLE users DROP COLUMN two_factor_enabled');
    }
    
    if (in_array('status', \$columnNames)) {
        echo 'Eliminando status...' . PHP_EOL;
        DB::statement('ALTER TABLE users DROP COLUMN status');
    }
    
    echo PHP_EOL . '✅ Estructura de tabla users actualizada correctamente' . PHP_EOL;
"

echo ""
echo "✅ Proceso completado!"
echo ""
echo "📋 Próximos pasos:"
echo "1. Ejecuta la migración de actualización: php artisan migrate --force"
echo "2. Vuelve al paso 4 del instalador: /install/finish"
echo ""
