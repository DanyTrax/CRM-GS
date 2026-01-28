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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('service_id')->nullable()->constrained()->onDelete('set null');
            
            // Detalles de facturación
            $table->enum('type', ['invoice', 'payment_receipt', 'billing_account'])->default('invoice');
            $table->text('concept')->nullable();
            
            // Montos y moneda
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('total', 15, 2);
            $table->enum('currency', ['USD', 'COP'])->default('COP');
            
            // Conversión USD -> COP (para Bold)
            $table->decimal('usd_amount', 15, 2)->nullable(); // Monto original en USD
            $table->decimal('exchange_rate', 10, 4)->nullable(); // TRM + Spread usado
            $table->decimal('cop_amount', 15, 2)->nullable(); // Monto convertido a COP
            
            // Estados y fechas
            $table->enum('status', ['draft', 'pending', 'paid', 'cancelled', 'refunded'])->default('draft');
            $table->date('issue_date');
            $table->date('due_date');
            $table->date('paid_at')->nullable();
            
            // Referencias
            $table->string('payment_method')->nullable(); // bold, manual, transfer, etc.
            $table->string('payment_reference')->nullable();
            $table->text('notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
