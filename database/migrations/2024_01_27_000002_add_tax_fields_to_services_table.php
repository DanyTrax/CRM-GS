<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            // Campos de impuesto
            $table->boolean('tax_enabled')->default(false)->after('price');
            $table->decimal('tax_percentage', 5, 2)->default(0)->after('tax_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['tax_enabled', 'tax_percentage']);
        });
    }
};
