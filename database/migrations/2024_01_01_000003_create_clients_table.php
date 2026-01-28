<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('company_name'); // Razón Social
            $table->string('tax_id')->nullable(); // NIT/Cédula
            $table->string('email_login'); // Email para acceso
            $table->string('email_billing')->nullable(); // Email para facturación
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->enum('status', ['borrador', 'activo', 'suspendido'])->default('borrador');
            $table->timestamps();
            $table->softDeletes(); // deleted_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
