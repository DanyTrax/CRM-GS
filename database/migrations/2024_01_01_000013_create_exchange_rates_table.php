<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->decimal('rate', 10, 4); // TRM USD a COP
            $table->string('source')->default('manual'); // manual, api
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique(['date', 'is_active']);
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
    }
};
