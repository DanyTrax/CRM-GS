<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('to_email');
            $table->string('subject');
            $table->text('body');
            $table->enum('status', ['queued', 'sent', 'failed'])->default('queued');
            $table->text('error_message')->nullable();
            $table->string('template_name')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            
            $table->index('client_id');
            $table->index('status');
            $table->index('sent_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_logs');
    }
};
