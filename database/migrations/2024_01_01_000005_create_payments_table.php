<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('method', ['bold', 'manual', 'bank_transfer', 'cash'])->default('bold');
            $table->enum('status', ['pending', 'approved', 'rejected', 'reviewing'])->default('pending');
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('COP');
            $table->decimal('exchange_rate', 10, 4)->nullable(); // TRM usada en conversión
            $table->string('transaction_id')->nullable(); // ID de Bold
            $table->string('payment_reference')->nullable();
            $table->date('payment_date');
            $table->text('notes')->nullable();
            $table->string('proof_file')->nullable(); // Archivo comprobante manual
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('invoice_id');
            $table->index('service_id');
            $table->index('status');
            $table->index('payment_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
