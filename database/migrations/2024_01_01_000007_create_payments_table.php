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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            
            // Información del pago
            $table->enum('method', ['bold', 'manual', 'transfer', 'cash'])->default('bold');
            $table->enum('status', ['pending', 'approved', 'rejected', 'refunded'])->default('pending');
            
            // Datos de Bold
            $table->string('bold_transaction_id')->nullable()->unique();
            $table->string('bold_reference')->nullable();
            $table->json('bold_response')->nullable(); // Respuesta completa de Bold
            $table->string('bold_signature')->nullable(); // Firma para validar webhook
            
            // Montos
            $table->decimal('amount', 15, 2);
            $table->enum('currency', ['USD', 'COP'])->default('COP');
            
            // Comprobante manual (si aplica)
            $table->string('payment_proof_path')->nullable();
            $table->text('payment_notes')->nullable();
            
            // Auditoría
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
