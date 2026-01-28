<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('email_templates', function (Blueprint $table) {
            // Agregar campos adicionales si no existen
            if (!Schema::hasColumn('email_templates', 'recipient_type')) {
                $table->enum('recipient_type', ['user', 'admin', 'both'])->default('user')->after('type');
            }
            
            if (!Schema::hasColumn('email_templates', 'variables')) {
                $table->json('variables')->nullable()->after('body');
            }
            
            if (!Schema::hasColumn('email_templates', 'example_data')) {
                $table->json('example_data')->nullable()->after('variables');
            }
            
            if (!Schema::hasColumn('email_templates', 'is_variation')) {
                $table->boolean('is_variation')->default(false)->after('is_active');
            }
            
            if (!Schema::hasColumn('email_templates', 'parent_template_id')) {
                $table->foreignId('parent_template_id')->nullable()->after('is_variation')->constrained('email_templates')->onDelete('cascade');
            }
            
            if (!Schema::hasColumn('email_templates', 'auto_send')) {
                $table->boolean('auto_send')->default(true)->after('is_variation');
            }
            
            if (!Schema::hasColumn('email_templates', 'trigger_conditions')) {
                $table->json('trigger_conditions')->nullable()->after('auto_send');
            }
        });
    }

    public function down(): void
    {
        Schema::table('email_templates', function (Blueprint $table) {
            if (Schema::hasColumn('email_templates', 'trigger_conditions')) {
                $table->dropColumn('trigger_conditions');
            }
            if (Schema::hasColumn('email_templates', 'auto_send')) {
                $table->dropColumn('auto_send');
            }
            if (Schema::hasColumn('email_templates', 'parent_template_id')) {
                $table->dropForeign(['parent_template_id']);
                $table->dropColumn('parent_template_id');
            }
            if (Schema::hasColumn('email_templates', 'is_variation')) {
                $table->dropColumn('is_variation');
            }
            if (Schema::hasColumn('email_templates', 'example_data')) {
                $table->dropColumn('example_data');
            }
            if (Schema::hasColumn('email_templates', 'variables')) {
                $table->dropColumn('variables');
            }
            if (Schema::hasColumn('email_templates', 'recipient_type')) {
                $table->dropColumn('recipient_type');
            }
        });
    }
};
