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
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['one_time', 'recurring'])->default('recurring');
            $table->enum('billing_cycle', ['monthly', 'quarterly', 'semiannual', 'annual', 'biannual', 'triennal'])->nullable();
            $table->decimal('price', 15, 2);
            $table->string('currency', 3)->default('COP'); // COP o USD
            $table->date('start_date');
            $table->date('current_due_date'); // Fecha de vencimiento actual
            $table->date('next_due_date')->nullable(); // Próxima fecha de vencimiento
            $table->enum('status', ['active', 'suspended', 'cancelled', 'expired'])->default('active');
            $table->json('credentials')->nullable(); // IP, Dominio, etc.
            $table->text('cancellation_reason')->nullable();
            $table->date('cancellation_requested_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('client_id');
            $table->index('status');
            $table->index('current_due_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
