<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->string('invoice_number')->unique();
            $table->string('prefix')->default('INV');
            $table->integer('consecutive_number');
            $table->enum('document_type', ['invoice', 'account_statement', 'receipt'])->default('invoice');
            $table->date('issue_date');
            $table->date('due_date');
            $table->decimal('subtotal', 15, 2);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total', 15, 2);
            $table->string('currency', 3)->default('COP');
            $table->decimal('exchange_rate', 10, 4)->nullable(); // TRM usada
            $table->decimal('spread_percentage', 5, 2)->nullable(); // Spread aplicado
            $table->enum('status', ['draft', 'pending', 'paid', 'overdue', 'cancelled'])->default('draft');
            $table->text('notes')->nullable();
            $table->json('tax_breakdown')->nullable(); // Desglose de impuestos
            $table->string('dian_resolution')->nullable(); // Resolución DIAN
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('client_id');
            $table->index('service_id');
            $table->index('status');
            $table->index('due_date');
            $table->index('invoice_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
