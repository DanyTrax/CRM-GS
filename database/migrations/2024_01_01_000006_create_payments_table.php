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
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->string('transaction_id')->nullable()->unique(); // Referencia Bold
            $table->enum('method', ['Bold', 'Transferencia', 'Efectivo'])->default('Bold');
            $table->string('proof_file')->nullable(); // Ruta imagen comprobante manual
            $table->decimal('amount_paid', 15, 2);
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
