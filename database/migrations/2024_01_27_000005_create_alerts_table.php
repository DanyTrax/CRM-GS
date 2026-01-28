<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nombre de la alerta
            $table->string('type'); // service_expiring, invoice_overdue, payment_pending, etc.
            $table->string('entity_type'); // Service, Invoice, Payment, etc.
            $table->unsignedBigInteger('entity_id'); // ID de la entidad relacionada
            $table->text('message'); // Mensaje de la alerta
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', ['pending', 'sent', 'dismissed', 'resolved'])->default('pending');
            $table->date('trigger_date'); // Fecha en que se debe activar
            $table->date('sent_at')->nullable(); // Fecha en que se envió
            $table->date('resolved_at')->nullable(); // Fecha en que se resolvió
            $table->json('metadata')->nullable(); // Datos adicionales
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['entity_type', 'entity_id']);
            $table->index('status');
            $table->index('trigger_date');
            $table->index('priority');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};
