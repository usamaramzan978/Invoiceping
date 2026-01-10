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
        Schema::create('message_templates', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();

            $table->string('name', 100); // e.g. "Default WhatsApp Reminder"
            $table->string('channel', 20)->default('whatsapp'); // ['whatsapp', 'sms']
            $table->longText('content');
            $table->string('type', 20)->nullable(); // reminder, welcome, followup, custom
            /*
              Example:
              Hi {client_name}, this is a reminder for invoice {invoice_number}
              of {amount} due on {due_date}.
              Please pay here: {invoice_link}
            */

            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['channel', 'type']);
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_templates');
    }
};
