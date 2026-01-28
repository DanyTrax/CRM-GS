<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->string('name'); // Ej: Hosting 5GB
            $table->enum('type', ['unico', 'recurrente'])->default('recurrente');
            $table->enum('currency', ['COP', 'USD'])->default('COP');
            $table->decimal('price', 15, 2);
            $table->integer('billing_cycle')->default(1); // 1, 3, 6, 12 meses
            $table->date('next_due_date'); // Fecha vencimiento - CRÍTICO
            $table->enum('status', ['activo', 'suspendido', 'cancelado'])->default('activo');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
