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
        Schema::create('support_tickets', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->uuid('business_id');
            $table->foreign('business_id')
                ->references('id')
                ->on('business_profiles')
                ->cascadeOnDelete();

            $table->uuid('client_id')->nullable();
            $table->foreign('client_id')
                ->references('id')
                ->on('clients')
                ->nullOnDelete();

            $table->uuid('invoice_id')->nullable();
            $table->foreign('invoice_id')
                ->references('id')
                ->on('invoices')
                ->nullOnDelete();

            $table->uuid('created_by');
            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();

            $table->string('subject', 200);
            $table->text('message');
            $table->string('attachment_path')->nullable();

            $table->string('status', 50)->default('open');
            $table->string('priority', 50)->default('medium');

            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_tickets');
    }
};
