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
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('entity_type', ['natural', 'juridical'])->default('natural');
            $table->string('name'); // Razón Social o Nombre
            $table->string('id_type'); // CC, NIT, etc.
            $table->string('id_number', 50)->unique(); // Aumentar longitud para NITs largos
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('phone')->nullable();
            $table->string('email'); // Para login y recuperación
            $table->string('billing_email')->nullable(); // Para facturas
            $table->enum('status', ['draft', 'active', 'suspended'])->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('user_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
