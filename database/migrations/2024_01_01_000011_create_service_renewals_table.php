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
        Schema::create('service_renewals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->foreignId('payment_id')->nullable()->constrained()->onDelete('set null');
            
            // Fechas de la renovación
            $table->date('previous_expiration_date'); // Fecha anterior de vencimiento
            $table->date('new_expiration_date'); // Nueva fecha calculada (anterior + periodo)
            $table->integer('months_added'); // Meses agregados en esta renovación
            
            // Cambio de ciclo (upselling)
            $table->integer('previous_cycle_months')->nullable();
            $table->integer('new_cycle_months')->nullable();
            $table->decimal('differential_amount', 15, 2)->nullable(); // Diferencial si cambió el ciclo
            
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_renewals');
    }
};
