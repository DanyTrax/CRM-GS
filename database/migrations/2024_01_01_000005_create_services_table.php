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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['unique', 'recurrent']); // Único o Recurrente
            $table->integer('billing_cycle_months')->default(1); // 1-36 meses para recurrentes
            $table->decimal('price', 15, 2); // Precio base
            $table->enum('currency', ['USD', 'COP'])->default('COP');
            $table->decimal('tax_rate', 5, 2)->default(0); // Porcentaje de impuesto (ej: 19 para IVA)
            
            // Fechas críticas para lógica anti-fraude
            $table->date('start_date');
            $table->date('last_renewal_date')->nullable(); // Fecha del último pago aprobado
            $table->date('expiration_date'); // Fecha de vencimiento actual
            $table->date('next_billing_date')->nullable(); // Próxima fecha de facturación
            
            $table->boolean('is_active')->default(true);
            $table->boolean('auto_renew')->default(true);
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
        Schema::dropIfExists('services');
    }
};
