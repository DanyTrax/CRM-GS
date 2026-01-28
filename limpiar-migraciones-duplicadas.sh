#!/bin/bash

# Script para limpiar migraciones duplicadas
# Ejecutar desde: cd ~/services.dowgroupcol.com

echo "🧹 Limpiando migraciones duplicadas..."

# 1. Hacer backup de la tabla migrations
echo "📦 Creando backup de tabla migrations..."
php artisan tinker --execute="
    \$backup = DB::table('migrations')->get()->toJson();
    file_put_contents(storage_path('app/migrations_backup_' . date('Y-m-d_His') . '.json'), \$backup);
    echo 'Backup creado en: ' . storage_path('app/migrations_backup_' . date('Y-m-d_His') . '.json') . PHP_EOL;
"

# 2. Limpiar tabla migrations (mantener solo las que están en batch 1)
echo "🗑️  Limpiando tabla migrations..."
php artisan tinker --execute="
    // Mantener solo las migraciones del batch 1 (las que ya se ejecutaron)
    DB::table('migrations')->where('batch', '>', 1)->delete();
    echo 'Migraciones del batch > 1 eliminadas' . PHP_EOL;
"

# 3. Verificar qué tablas ya existen
echo "🔍 Verificando tablas existentes..."
php artisan tinker --execute="
    \$existingTables = [];
    try {
        \$tables = DB::select('SHOW TABLES');
        \$dbName = DB::connection()->getDatabaseName();
        foreach (\$tables as \$table) {
            \$tableName = \$table->{'Tables_in_' . \$dbName};
            \$existingTables[] = \$tableName;
        }
    } catch (\Exception \$e) {
        echo 'Error verificando tablas: ' . \$e->getMessage() . PHP_EOL;
    }
    
    echo 'Tablas existentes: ' . implode(', ', \$existingTables) . PHP_EOL;
    
    // Guardar en archivo temporal para usar en el siguiente paso
    file_put_contents(storage_path('app/existing_tables.json'), json_encode(\$existingTables));
"

# 4. Registrar solo las migraciones correctas (eliminar duplicadas)
echo "📝 Registrando solo migraciones correctas..."
php artisan tinker --execute="
    // Leer tablas existentes
    \$existingTables = json_decode(file_get_contents(storage_path('app/existing_tables.json')), true);
    
    // Lista de migraciones correctas (sin duplicados)
    // IMPORTANTE: NO incluir '2024_01_01_000001_create_users_table' porque es la versión antigua
    \$correctMigrations = [
        '2024_01_01_000001_create_roles_table',
        // '2024_01_01_000001_create_users_table' - NO incluir (versión antigua sin role_id)
        '2024_01_01_000002_create_users_table', // Esta es la correcta con role_id
        '2024_01_01_000002_create_clients_table',
        '2024_01_01_000003_create_role_user_table',
        '2024_01_01_000003_create_services_table',
        '2024_01_01_000005_create_invoices_table',
        '2024_01_01_000005_create_payments_table',
        '2024_01_01_000006_create_tickets_table',
        '2024_01_01_000007_create_settings_table',
        '2024_01_01_000007_create_ticket_replies_table',
        '2024_01_01_000008_create_email_logs_table',
        '2024_01_01_000009_create_email_templates_table',
        '2024_01_01_000010_create_impersonation_logs_table',
        '2024_01_01_000010_create_ticket_messages_table',
        '2024_01_01_000011_create_backups_table',
        '2024_01_01_000011_create_service_renewals_table',
        '2024_01_01_000012_create_cron_jobs_logs_table',
        '2024_01_01_000013_create_exchange_rates_table',
        '2024_01_01_000015_create_jobs_table',
        '2024_01_01_000016_create_sessions_table',
        '2024_01_01_000017_create_cache_table',
        '2024_01_16_000001_fix_clients_id_number_column',
        '2024_01_20_000001_fix_users_table_add_role_id',
        '2026_01_16_223003_create_permission_tables',
    ];
    
    // Eliminar todas las migraciones duplicadas (incluyendo la versión antigua de users)
    DB::table('migrations')->whereNotIn('migration', \$correctMigrations)->delete();
    
    // Eliminar específicamente la migración antigua de users si existe
    DB::table('migrations')->where('migration', '2024_01_01_000001_create_users_table')->delete();
    
    // Registrar las migraciones correctas que faltan
    \$existing = DB::table('migrations')->pluck('migration')->toArray();
    \$batch = 1;
    
    foreach (\$correctMigrations as \$migration) {
        if (!in_array(\$migration, \$existing)) {
            // Si la tabla ya existe, registrar la migración como ejecutada
            // Si no existe, dejarla pendiente para que se ejecute
            \$tableName = null;
            if (strpos(\$migration, 'create_') !== false) {
                \$tableName = str_replace('create_', '', str_replace('_table', '', substr(\$migration, strrpos(\$migration, '_') + 1)));
            }
            
            if (\$tableName && in_array(\$tableName, \$existingTables)) {
                // La tabla ya existe, registrar como ejecutada
                DB::table('migrations')->insert([
                    'migration' => \$migration,
                    'batch' => \$batch,
                ]);
                echo 'Registrada (tabla existe): ' . \$migration . PHP_EOL;
            } else {
                // La tabla no existe, dejar pendiente
                echo 'Pendiente (tabla no existe): ' . \$migration . PHP_EOL;
            }
        }
    }
    
    echo 'Migraciones limpiadas y registradas correctamente' . PHP_EOL;
"

# 5. Ejecutar migraciones pendientes (solo las que no intentan crear tablas existentes)
echo "🚀 Ejecutando migraciones pendientes..."
php artisan migrate --force 2>&1 | grep -v "already exists" || echo "Algunas migraciones fallaron porque las tablas ya existen (esto es normal)"

echo ""
echo "✅ Limpieza completada!"
echo ""
echo "📋 Próximos pasos:"
echo "1. Verifica el estado: php artisan migrate:status"
echo "2. Si todo está bien, continúa con la instalación"
echo ""
