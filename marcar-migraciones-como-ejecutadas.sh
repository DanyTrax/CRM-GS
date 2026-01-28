#!/bin/bash

# Script para marcar migraciones duplicadas como ejecutadas (si las tablas ya existen)
# Ejecutar desde: cd ~/services.dowgroupcol.com

echo "📝 Marcando migraciones duplicadas como ejecutadas..."

php artisan tinker --execute="
    // Lista de migraciones duplicadas que deben marcarse como ejecutadas
    // porque las tablas ya existen (creadas por otras migraciones)
    \$duplicateMigrations = [
        '2024_01_01_000003_create_clients_table', // Tabla ya existe (creada por 2024_01_01_000002_create_clients_table)
        '2024_01_01_000004_create_clients_table', // Tabla ya existe
        '2024_01_01_000004_create_services_table', // Tabla ya existe (creada por 2024_01_01_000003_create_services_table)
        '2024_01_01_000004_create_invoices_table', // Tabla ya existe (creada por 2024_01_01_000005_create_invoices_table)
        '2024_01_01_000005_create_services_table', // Tabla ya existe
        '2024_01_01_000006_create_invoices_table', // Tabla ya existe
        '2024_01_01_000006_create_payments_table', // Tabla ya existe (creada por 2024_01_01_000005_create_payments_table)
        '2024_01_01_000007_create_payments_table', // Tabla ya existe
        '2024_01_01_000009_create_tickets_table', // Tabla ya existe (creada por 2024_01_01_000006_create_tickets_table)
        '2024_01_01_000012_create_settings_table', // Tabla ya existe (creada por 2024_01_01_000007_create_settings_table)
        '2024_01_01_000014_create_settings_table', // Tabla ya existe
    ];
    
    \$batch = 1;
    \$registered = 0;
    
    foreach (\$duplicateMigrations as \$migration) {
        // Verificar si ya está registrada
        \$exists = DB::table('migrations')
            ->where('migration', \$migration)
            ->exists();
        
        if (!\$exists) {
            // Registrar como ejecutada
            DB::table('migrations')->insert([
                'migration' => \$migration,
                'batch' => \$batch,
            ]);
            \$registered++;
            echo 'Registrada: ' . \$migration . PHP_EOL;
        }
    }
    
    echo PHP_EOL . 'Total migraciones registradas: ' . \$registered . PHP_EOL;
    echo '✅ Migraciones duplicadas marcadas como ejecutadas' . PHP_EOL;
"

echo ""
echo "✅ Proceso completado!"
echo ""
echo "📋 Verifica el estado:"
echo "   php artisan migrate:status"
echo ""
