#!/bin/bash

# Script para ejecutar migraciones faltantes
# Ejecutar desde: cd ~/services.dowgroupcol.com

echo "🔧 Ejecutando migraciones faltantes..."

# Verificar estado de migraciones
echo "📋 Estado actual de migraciones:"
php artisan migrate:status | tail -20

echo ""
echo "🔍 Verificando tablas faltantes..."

php artisan tinker --execute="
    try {
        \$tables = [
            'clients' => 'Tabla de clientes',
            'services' => 'Tabla de servicios',
            'invoices' => 'Tabla de facturas',
            'payments' => 'Tabla de pagos',
            'tickets' => 'Tabla de tickets',
            'settings' => 'Tabla de configuraciones',
        ];
        
        echo 'Verificando tablas...' . PHP_EOL;
        echo '' . PHP_EOL;
        
        foreach (\$tables as \$table => \$description) {
            if (DB::getSchemaBuilder()->hasTable(\$table)) {
                echo '✅ ' . \$table . ' - ' . \$description . PHP_EOL;
            } else {
                echo '❌ ' . \$table . ' - ' . \$description . ' (FALTA)' . PHP_EOL;
            }
        }
    } catch (\Exception \$e) {
        echo '❌ Error al verificar tablas: ' . \$e->getMessage() . PHP_EOL;
    }
"

echo ""
echo "🚀 Ejecutando migraciones..."
php artisan migrate --force

echo ""
echo "✅ Migraciones ejecutadas!"
echo ""
echo "📋 Verificando tablas nuevamente..."

php artisan tinker --execute="
    try {
        \$tables = ['clients', 'services', 'invoices', 'payments', 'tickets', 'settings'];
        
        foreach (\$tables as \$table) {
            if (DB::getSchemaBuilder()->hasTable(\$table)) {
                \$count = DB::table(\$table)->count();
                echo '✅ ' . \$table . ' - ' . \$count . ' registros' . PHP_EOL;
            } else {
                echo '❌ ' . \$table . ' - NO EXISTE' . PHP_EOL;
            }
        }
    } catch (\Exception \$e) {
        echo '❌ Error: ' . \$e->getMessage() . PHP_EOL;
    }
"

echo ""
echo "✅ Proceso completado!"
echo ""
