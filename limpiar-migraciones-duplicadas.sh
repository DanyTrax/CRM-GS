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

# 3. Registrar solo las migraciones correctas (eliminar duplicadas)
echo "📝 Registrando solo migraciones correctas..."
php artisan tinker --execute="
    // Lista de migraciones correctas (sin duplicados)
    \$correctMigrations = [
        '2024_01_01_000001_create_roles_table',
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
    
    // Eliminar todas las migraciones duplicadas
    DB::table('migrations')->whereNotIn('migration', \$correctMigrations)->delete();
    
    // Registrar las migraciones correctas que faltan
    \$existing = DB::table('migrations')->pluck('migration')->toArray();
    \$batch = 1;
    
    foreach (\$correctMigrations as \$migration) {
        if (!in_array(\$migration, \$existing)) {
            DB::table('migrations')->insert([
                'migration' => \$migration,
                'batch' => \$batch,
            ]);
        }
    }
    
    echo 'Migraciones limpiadas y registradas correctamente' . PHP_EOL;
"

# 4. Ejecutar migraciones pendientes
echo "🚀 Ejecutando migraciones pendientes..."
php artisan migrate --force

echo ""
echo "✅ Limpieza completada!"
echo ""
echo "📋 Próximos pasos:"
echo "1. Verifica el estado: php artisan migrate:status"
echo "2. Si todo está bien, continúa con la instalación"
echo ""
