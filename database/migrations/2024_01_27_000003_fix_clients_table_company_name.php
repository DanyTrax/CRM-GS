<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Usar DB::statement para renombrar columnas (MySQL no soporta renameColumn directamente)
        if (Schema::hasColumn('clients', 'name') && !Schema::hasColumn('clients', 'company_name')) {
            \Illuminate\Support\Facades\DB::statement('ALTER TABLE `clients` CHANGE `name` `company_name` VARCHAR(255) NOT NULL');
        }
        
        if (Schema::hasColumn('clients', 'email') && !Schema::hasColumn('clients', 'email_login')) {
            \Illuminate\Support\Facades\DB::statement('ALTER TABLE `clients` CHANGE `email` `email_login` VARCHAR(255) NOT NULL');
        }
        
        if (Schema::hasColumn('clients', 'billing_email') && !Schema::hasColumn('clients', 'email_billing')) {
            \Illuminate\Support\Facades\DB::statement('ALTER TABLE `clients` CHANGE `billing_email` `email_billing` VARCHAR(255) NULL');
        }
        
        if (Schema::hasColumn('clients', 'id_number') && !Schema::hasColumn('clients', 'tax_id')) {
            \Illuminate\Support\Facades\DB::statement('ALTER TABLE `clients` CHANGE `id_number` `tax_id` VARCHAR(50) NULL');
        }
    }

    public function down(): void
    {
        // Revertir cambios
        if (Schema::hasColumn('clients', 'company_name') && !Schema::hasColumn('clients', 'name')) {
            \Illuminate\Support\Facades\DB::statement('ALTER TABLE `clients` CHANGE `company_name` `name` VARCHAR(255) NOT NULL');
        }
        
        if (Schema::hasColumn('clients', 'email_login') && !Schema::hasColumn('clients', 'email')) {
            \Illuminate\Support\Facades\DB::statement('ALTER TABLE `clients` CHANGE `email_login` `email` VARCHAR(255) NOT NULL');
        }
        
        if (Schema::hasColumn('clients', 'email_billing') && !Schema::hasColumn('clients', 'billing_email')) {
            \Illuminate\Support\Facades\DB::statement('ALTER TABLE `clients` CHANGE `email_billing` `billing_email` VARCHAR(255) NULL');
        }
        
        if (Schema::hasColumn('clients', 'tax_id') && !Schema::hasColumn('clients', 'id_number')) {
            \Illuminate\Support\Facades\DB::statement('ALTER TABLE `clients` CHANGE `tax_id` `id_number` VARCHAR(50) NULL');
        }
    }
};
