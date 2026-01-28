<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Si la tabla no existe, crearla primero con todos los campos
        if (!Schema::hasTable('email_templates')) {
            Schema::create('email_templates', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('subject');
                $table->text('body');
                $table->string('type')->default('general');
                $table->enum('recipient_type', ['user', 'admin', 'both'])->default('user');
                $table->json('variables')->nullable();
                $table->json('example_data')->nullable();
                $table->boolean('is_active')->default(true);
                $table->boolean('is_variation')->default(false);
                $table->foreignId('parent_template_id')->nullable()->constrained('email_templates')->onDelete('cascade');
                $table->boolean('auto_send')->default(true);
                $table->json('trigger_conditions')->nullable();
                $table->timestamps();
            });
        } else {
            // Si la tabla existe, solo agregar los campos que faltan
            Schema::table('email_templates', function (Blueprint $table) {
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
    }

    public function down(): void
    {
        // Si la tabla fue creada por esta migración, eliminarla
        if (Schema::hasTable('email_templates')) {
            // Verificar si tiene los campos nuevos (significa que fue creada por esta migración)
            if (Schema::hasColumn('email_templates', 'recipient_type')) {
                // Si solo tiene los campos nuevos, eliminar la tabla completa
                // Pero primero verificar si tiene los campos originales
                if (!Schema::hasColumn('email_templates', 'type') || Schema::hasColumn('email_templates', 'recipient_type')) {
                    // Solo eliminar columnas agregadas, no la tabla completa
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
            }
        }
    }
};
