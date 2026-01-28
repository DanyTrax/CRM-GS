<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backups', function (Blueprint $table) {
            $table->id();
            $table->string('filename');
            $table->string('path');
            $table->bigInteger('size'); // En bytes
            $table->enum('status', ['pending', 'completed', 'failed', 'uploaded'])->default('pending');
            $table->enum('storage_type', ['local', 'drive', 'onedrive'])->default('local');
            $table->string('cloud_file_id')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backups');
    }
};
