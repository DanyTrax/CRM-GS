-- ============================================
-- SQL para crear tablas faltantes manualmente
-- Ejecutar en phpMyAdmin
-- ============================================

-- 1. TABLA: services
CREATE TABLE IF NOT EXISTS `services` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `client_id` bigint unsigned NOT NULL,
    `name` varchar(255) NOT NULL,
    `description` text,
    `type` enum('unico','recurrente') NOT NULL DEFAULT 'recurrente',
    `currency` enum('COP','USD') NOT NULL DEFAULT 'COP',
    `price` decimal(15,2) NOT NULL,
    `billing_cycle` int NOT NULL DEFAULT 1,
    `next_due_date` date NOT NULL,
    `status` enum('activo','suspendido','cancelado') NOT NULL DEFAULT 'activo',
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    `deleted_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `services_client_id_foreign` (`client_id`),
    CONSTRAINT `services_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. TABLA: invoices
CREATE TABLE IF NOT EXISTS `invoices` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `client_id` bigint unsigned NOT NULL,
    `invoice_number` varchar(255) NOT NULL,
    `total_amount` decimal(15,2) NOT NULL,
    `currency` enum('COP','USD') NOT NULL DEFAULT 'COP',
    `trm_snapshot` decimal(10,4) DEFAULT NULL,
    `status` enum('borrador','pendiente','pagada','anulada') NOT NULL DEFAULT 'borrador',
    `pdf_template` enum('legal','cuenta_cobro') NOT NULL DEFAULT 'legal',
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. TABLA: payments
CREATE TABLE IF NOT EXISTS `payments` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `invoice_id` bigint unsigned NOT NULL,
    `transaction_id` varchar(255) DEFAULT NULL,
    `method` enum('Bold','Transferencia','Efectivo') NOT NULL DEFAULT 'Bold',
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. TABLA: tickets
CREATE TABLE IF NOT EXISTS `tickets` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `client_id` bigint unsigned NOT NULL,
    `ticket_number` varchar(255) NOT NULL,
    `subject` varchar(255) NOT NULL,
    `description` text NOT NULL,
    `priority` enum('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
    `status` enum('open','in_progress','resolved','closed') NOT NULL DEFAULT 'open',
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    `deleted_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `tickets_ticket_number_unique` (`ticket_number`),
    KEY `tickets_client_id_foreign` (`client_id`),
    CONSTRAINT `tickets_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Verificar que las tablas se crearon
-- ============================================
-- Ejecuta esto después para verificar:
-- SHOW TABLES LIKE 'services';
-- SHOW TABLES LIKE 'invoices';
-- SHOW TABLES LIKE 'payments';
-- SHOW TABLES LIKE 'tickets';
