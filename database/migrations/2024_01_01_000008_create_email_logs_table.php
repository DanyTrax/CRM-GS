<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->string('to');
            $table->string('cc')->nullable();
            $table->string('bcc')->nullable();
            $table->string('subject');
            $table->longText('body'); // HTML del correo enviado
            $table->longText('original_body')->nullable(); // Cuerpo original antes de edición
            $table->boolean('was_edited')->default(false); // Indica si fue editado por el interceptor
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            $table->string('message_id')->nullable(); // ID del mensaje del servidor de correo
            $table->text('error_message')->nullable();
            
            // Relaciones opcionales
            $table->string('mailable_type')->nullable(); // Clase del Mailable
            $table->unsignedBigInteger('mailable_id')->nullable();
            
            // Usuario que envió/interceptó
            $table->foreignId('sent_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Contexto de migración silenciosa
            $table->boolean('suppressed_by_migration_mode')->default(false);
            
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            
            $table->index(['mailable_type', 'mailable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_logs');
    }
};
