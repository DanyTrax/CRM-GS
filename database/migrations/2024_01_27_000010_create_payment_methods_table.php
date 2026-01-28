<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nombre del medio de pago (ej: "Bold", "Transferencia Bancaria", "Efectivo")
            $table->string('slug')->unique(); // Identificador único (ej: "bold", "bank_transfer", "cash")
            $table->string('type')->default('manual'); // automatic, manual, gateway
            $table->string('provider')->nullable(); // bold, payu, stripe, etc.
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            
            // Configuración del medio de pago
            $table->json('configuration')->nullable(); // Configuración específica (API keys, webhooks, etc.)
            $table->json('settings')->nullable(); // Configuraciones adicionales
            
            // Información para el usuario
            $table->text('description')->nullable();
            $table->text('instructions')->nullable(); // Instrucciones para el cliente
            $table->string('icon')->nullable(); // Icono o imagen del medio de pago
            
            // Configuración de comisiones
            $table->decimal('fee_percentage', 5, 2)->default(0.00); // Comisión porcentual
            $table->decimal('fee_fixed', 10, 2)->default(0.00); // Comisión fija
            
            // Configuración de límites
            $table->decimal('min_amount', 15, 2)->nullable(); // Monto mínimo
            $table->decimal('max_amount', 15, 2)->nullable(); // Monto máximo
            
            // Configuración de monedas aceptadas
            $table->json('accepted_currencies')->nullable(); // ['COP', 'USD']
            
            // Configuración de aprobación
            $table->boolean('requires_approval')->default(false); // Requiere aprobación manual
            $table->boolean('auto_approve')->default(false); // Aprobación automática
            
            // Configuración de webhooks
            $table->string('webhook_url')->nullable();
            $table->string('webhook_secret')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('slug');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
