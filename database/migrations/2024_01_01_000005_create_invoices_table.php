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
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->string('invoice_number')->unique(); // Consecutivo: FAC-001
            $table->decimal('total_amount', 15, 2);
            $table->enum('currency', ['COP', 'USD'])->default('COP');
            $table->decimal('trm_snapshot', 10, 4)->nullable(); // TRM usada si fue conversión USD->COP
            $table->enum('status', ['borrador', 'pendiente', 'pagada', 'anulada'])->default('borrador');
            $table->enum('pdf_template', ['legal', 'cuenta_cobro'])->default('legal');
            $table->date('issue_date');
            $table->date('due_date');
            $table->text('concept')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
