<?php

declare(strict_types=1);

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
        Schema::create('logs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->nullable()->index();
            $table->uuid('invoice_id')->nullable()->index();
            $table->string('type', 50)->index(); // 'email', 'whatsapp', 'sms', 'error', 'info', 'warning'
            $table->string('status', 20)->index(); // 'success', 'failed', 'pending', 'error'
            $table->string('channel', 20)->nullable()->index(); // 'email', 'whatsapp', 'sms'
            $table->string('recipient', 255)->nullable(); // email or phone number
            $table->string('subject', 500)->nullable(); // email subject
            $table->text('content')->nullable(); // message content
            $table->text('error_message')->nullable(); // error details if failed
            $table->json('metadata')->nullable(); // additional data (template_id, message_length, etc.)
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('sent_at')->nullable()->index(); // when message was sent
            $table->timestamps();

            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('set null');

            // Indexes for performance
            $table->index(['user_id', 'type', 'status']);
            $table->index(['user_id', 'channel', 'status']);
            $table->index(['created_at', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logs');
    }
};
