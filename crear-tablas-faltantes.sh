#!/bin/bash

# Script para crear tablas faltantes manualmente
# Ejecutar desde: cd ~/services.dowgroupcol.com

echo "🔧 Creando tablas faltantes..."

php artisan tinker --execute="
    try {
        // Crear tabla services
        if (!DB::getSchemaBuilder()->hasTable('services')) {
            echo '📋 Creando tabla services...' . PHP_EOL;
            DB::statement('
                CREATE TABLE IF NOT EXISTS `services` (
                    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                    `client_id` bigint unsigned NOT NULL,
                    `name` varchar(255) NOT NULL,
                    `description` text,
                    `type` enum(\"unico\",\"recurrente\") NOT NULL DEFAULT \"recurrente\",
                    `currency` enum(\"COP\",\"USD\") NOT NULL DEFAULT \"COP\",
                    `price` decimal(15,2) NOT NULL,
                    `billing_cycle` int NOT NULL DEFAULT 1,
                    `next_due_date` date NOT NULL,
                    `status` enum(\"activo\",\"suspendido\",\"cancelado\") NOT NULL DEFAULT \"activo\",
                    `created_at` timestamp NULL DEFAULT NULL,
                    `updated_at` timestamp NULL DEFAULT NULL,
                    `deleted_at` timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `services_client_id_foreign` (`client_id`),
                    CONSTRAINT `services_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ');
            echo '✅ Tabla services creada' . PHP_EOL;
        }
        
        // Crear tabla invoices
        if (!DB::getSchemaBuilder()->hasTable('invoices')) {
            echo '📋 Creando tabla invoices...' . PHP_EOL;
            DB::statement('
                CREATE TABLE IF NOT EXISTS `invoices` (
                    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                    `client_id` bigint unsigned NOT NULL,
                    `invoice_number` varchar(255) NOT NULL,
                    `total_amount` decimal(15,2) NOT NULL,
                    `currency` enum(\"COP\",\"USD\") NOT NULL DEFAULT \"COP\",
                    `trm_snapshot` decimal(10,4) DEFAULT NULL,
                    `status` enum(\"borrador\",\"pendiente\",\"pagada\",\"anulada\") NOT NULL DEFAULT \"borrador\",
                    `pdf_template` enum(\"legal\",\"cuenta_cobro\") NOT NULL DEFAULT \"legal\",
                    `issue_date` date NOT NULL,
                    `due_date` date NOT NULL,
                    `concept` text,
                    `created_at` timestamp NULL DEFAULT NULL,
                    `updated_at` timestamp NULL DEFAULT NULL,
                    `deleted_at` timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `invoices_invoice_number_unique` (`invoice_number`),
                    KEY `invoices_client_id_foreign` (`client_id`),
                    CONSTRAINT `invoices_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ');
            echo '✅ Tabla invoices creada' . PHP_EOL;
        }
        
        // Crear tabla payments
        if (!DB::getSchemaBuilder()->hasTable('payments')) {
            echo '📋 Creando tabla payments...' . PHP_EOL;
            DB::statement('
                CREATE TABLE IF NOT EXISTS `payments` (
                    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                    `invoice_id` bigint unsigned NOT NULL,
                    `transaction_id` varchar(255) DEFAULT NULL,
                    `method` enum(\"Bold\",\"Transferencia\",\"Efectivo\") NOT NULL DEFAULT \"Bold\",
                    `proof_file` varchar(255) DEFAULT NULL,
                    `amount_paid` decimal(15,2) NOT NULL,
                    `approved_at` timestamp NULL DEFAULT NULL,
                    `created_at` timestamp NULL DEFAULT NULL,
                    `updated_at` timestamp NULL DEFAULT NULL,
                    `deleted_at` timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `payments_transaction_id_unique` (`transaction_id`),
                    KEY `payments_invoice_id_foreign` (`invoice_id`),
                    CONSTRAINT `payments_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ');
            echo '✅ Tabla payments creada' . PHP_EOL;
        }
        
        // Crear tabla tickets
        if (!DB::getSchemaBuilder()->hasTable('tickets')) {
            echo '📋 Creando tabla tickets...' . PHP_EOL;
            DB::statement('
                CREATE TABLE IF NOT EXISTS `tickets` (
                    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                    `client_id` bigint unsigned NOT NULL,
                    `ticket_number` varchar(255) NOT NULL,
                    `subject` varchar(255) NOT NULL,
                    `description` text NOT NULL,
                    `priority` enum(\"low\",\"medium\",\"high\",\"urgent\") NOT NULL DEFAULT \"medium\",
                    `status` enum(\"open\",\"in_progress\",\"resolved\",\"closed\") NOT NULL DEFAULT \"open\",
                    `created_at` timestamp NULL DEFAULT NULL,
                    `updated_at` timestamp NULL DEFAULT NULL,
                    `deleted_at` timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `tickets_ticket_number_unique` (`ticket_number`),
                    KEY `tickets_client_id_foreign` (`client_id`),
                    CONSTRAINT `tickets_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ');
            echo '✅ Tabla tickets creada' . PHP_EOL;
        }
        
        echo '' . PHP_EOL;
        echo '✅ Todas las tablas creadas!' . PHP_EOL;
        
    } catch (\Exception \$e) {
        echo '❌ Error: ' . \$e->getMessage() . PHP_EOL;
        exit(1);
    }
"

echo ""
echo "✅ Proceso completado!"
echo ""
