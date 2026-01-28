<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('message_history', function (Blueprint $table) {
            $table->id();
            $table->string('message_type'); // email, sms, push, in_app
            $table->string('recipient_type'); // user, admin, both
            $table->unsignedBigInteger('recipient_id')->nullable(); // ID del usuario/cliente
            $table->string('recipient_email')->nullable();
            $table->string('recipient_phone')->nullable();
            $table->string('subject')->nullable();
            $table->text('body');
            $table->string('template_type')->nullable(); // Tipo de plantilla usada
            $table->unsignedBigInteger('template_id')->nullable(); // ID de la plantilla
            $table->enum('status', ['pending', 'sent', 'failed', 'delivered', 'read'])->default('pending');
            $table->string('provider')->nullable(); // smtp, zoho, twilio, etc.
            $table->string('external_id')->nullable(); // ID del mensaje en el proveedor externo
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable(); // Datos adicionales
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->unsignedBigInteger('sent_by')->nullable(); // Usuario que envió
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['recipient_type', 'recipient_id']);
            $table->index('status');
            $table->index('message_type');
            $table->index('sent_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('message_history');
    }
};
