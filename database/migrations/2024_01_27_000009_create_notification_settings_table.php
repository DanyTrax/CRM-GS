<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_settings', function (Blueprint $table) {
            $table->id();
            $table->string('module'); // service, invoice, payment, ticket, user, etc.
            $table->string('event_type'); // created, updated, expired, cancelled, etc.
            $table->enum('recipient_type', ['user', 'admin', 'both'])->default('user');
            $table->boolean('email_enabled')->default(true);
            $table->boolean('sms_enabled')->default(false);
            $table->boolean('push_enabled')->default(false);
            $table->boolean('in_app_enabled')->default(true);
            $table->unsignedBigInteger('template_id')->nullable(); // Plantilla de email a usar
            $table->json('conditions')->nullable(); // Condiciones adicionales
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->unique(['module', 'event_type', 'recipient_type']);
            $table->index('module');
            $table->index('recipient_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_settings');
    }
};
