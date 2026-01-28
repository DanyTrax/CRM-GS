#!/bin/bash

# Script para crear la tabla settings
# Ejecutar desde: cd ~/services.dowgroupcol.com

echo "🔧 Creando tabla settings..."

php artisan tinker --execute="
    try {
        // Verificar si la tabla settings existe
        if (DB::getSchemaBuilder()->hasTable('settings')) {
            echo '✅ La tabla settings ya existe' . PHP_EOL;
        } else {
            echo '📋 Creando tabla settings...' . PHP_EOL;
            
            DB::statement('
                CREATE TABLE IF NOT EXISTS `settings` (
                    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                    `key` varchar(255) NOT NULL,
                    `value` text,
                    `type` varchar(255) NOT NULL DEFAULT \"string\",
                    `description` text,
                    `created_at` timestamp NULL DEFAULT NULL,
                    `updated_at` timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `settings_key_unique` (`key`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ');
            
            echo '✅ Tabla settings creada exitosamente' . PHP_EOL;
        }
        
        // Ejecutar seeder de settings si existe
        echo '' . PHP_EOL;
        echo '📋 Ejecutando seeder de settings...' . PHP_EOL;
        
        try {
            Artisan::call('db:seed', ['--class' => 'SettingsSeeder', '--force' => true]);
            echo '✅ SettingsSeeder ejecutado' . PHP_EOL;
        } catch (\Exception \$e) {
            echo '⚠️  SettingsSeeder no se pudo ejecutar: ' . \$e->getMessage() . PHP_EOL;
            echo '   Creando settings básicos manualmente...' . PHP_EOL;
            
            // Crear algunos settings básicos
            \$basicSettings = [
                ['key' => 'app_name', 'value' => 'CRM Services', 'type' => 'string', 'description' => 'Nombre de la aplicación'],
                ['key' => 'company_name', 'value' => 'DOW Group', 'type' => 'string', 'description' => 'Nombre de la empresa'],
                ['key' => 'last_schedule_run', 'value' => now()->toDateTimeString(), 'type' => 'string', 'description' => 'Última ejecución del cron'],
            ];
            
            foreach (\$basicSettings as \$setting) {
                DB::table('settings')->updateOrInsert(
                    ['key' => \$setting['key']],
                    array_merge(\$setting, [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ])
                );
            }
            
            echo '✅ Settings básicos creados' . PHP_EOL;
        }
        
        echo '' . PHP_EOL;
        echo '✅ Proceso completado!' . PHP_EOL;
        
    } catch (\Exception \$e) {
        echo '❌ Error: ' . \$e->getMessage() . PHP_EOL;
        echo '   Trace: ' . \$e->getTraceAsString() . PHP_EOL;
        exit(1);
    }
"

echo ""
echo "✅ Tabla settings creada!"
echo ""
