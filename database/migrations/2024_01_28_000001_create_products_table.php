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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('type', ['one_time', 'recurring'])->default('recurring');
            $table->integer('duration_value')->nullable()->comment('Valor de duración (ej: 1, 12, etc.)');
            $table->enum('duration_unit', ['days', 'months', 'years'])->nullable()->comment('Unidad de duración');
            $table->decimal('price', 15, 2);
            $table->enum('currency', ['COP', 'USD'])->default('COP');
            $table->boolean('tax_enabled')->default(false);
            $table->decimal('tax_percentage', 5, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
