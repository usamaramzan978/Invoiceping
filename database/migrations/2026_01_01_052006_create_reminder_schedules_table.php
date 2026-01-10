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
        Schema::create('reminder_schedules', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->uuid('invoice_id');
            $table->foreign('invoice_id')
                ->references('id')
                ->on('invoices')
                ->cascadeOnDelete();

            $table->uuid('reminder_rule_id')->nullable();
            $table->uuid('reminder_rule_step_id')->nullable();
            $table->uuid('message_template_id')->nullable();
            $table->uuid('bulk_group_id')->nullable();

            $table->string('channel'); // email | whatsapp
            $table->string('source_type')->nullable(); // 'manual', 'rule'

            $table->dateTime('scheduled_at');

            $table->string('status')->default('pending'); // pending | sent | failed | skipped | cancelled

            $table->timestamp('sent_at')->nullable();
            $table->text('failure_reason')->nullable();

            $table->timestamps();

            $table->index(['status', 'scheduled_at']);
            $table->index(['invoice_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reminder_schedules');
    }
};
