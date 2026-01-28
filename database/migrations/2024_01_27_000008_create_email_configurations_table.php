<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nombre de la configuración (ej: "SMTP Principal", "Zoho")
            $table->enum('provider', ['smtp', 'zoho', 'sendgrid', 'mailgun'])->default('smtp');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            
            // Configuración SMTP
            $table->string('smtp_host')->nullable();
            $table->integer('smtp_port')->nullable();
            $table->string('smtp_encryption')->nullable(); // tls, ssl, null
            $table->string('smtp_username')->nullable();
            $table->string('smtp_password')->nullable();
            
            // Configuración Zoho
            $table->string('zoho_client_id')->nullable();
            $table->string('zoho_client_secret')->nullable();
            $table->text('zoho_refresh_token')->nullable();
            $table->text('zoho_access_token')->nullable();
            $table->timestamp('zoho_token_expires_at')->nullable();
            
            // Configuración general
            $table->string('from_email');
            $table->string('from_name');
            $table->string('reply_to_email')->nullable();
            $table->string('reply_to_name')->nullable();
            
            // Configuración adicional
            $table->integer('rate_limit')->default(100); // Emails por hora
            $table->json('settings')->nullable(); // Configuraciones adicionales
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_configurations');
    }
};
