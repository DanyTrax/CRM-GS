<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nombre de la plantilla (ej: "Factura Legal", "Remisión", "Cuenta de Cobro")
            $table->string('type')->default('invoice'); // invoice, remision, cuenta_cobro
            $table->text('html_content'); // Contenido HTML de la plantilla
            $table->json('variables')->nullable(); // Variables disponibles (ej: {client_name}, {invoice_number})
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false); // Plantilla por defecto
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_templates');
    }
};
