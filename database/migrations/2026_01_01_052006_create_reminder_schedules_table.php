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
            $table->foreign('invoice_id')->references('id')->on('invoices')->cascadeOnDelete();

            $table->uuid('reminder_rule_id')->nullable();
            $table->foreign('reminder_rule_id')
                ->references('id')
                ->on('reminder_rules')
                ->nullOnDelete();

            $table->uuid('reminder_rule_step_id')->nullable();
            $table->foreign('reminder_rule_step_id')
                ->references('id')
                ->on('reminder_rule_steps')
                ->nullOnDelete();

            $table->uuid('message_template_id')->nullable();
            $table->foreign('message_template_id')
                ->references('id')
                ->on('message_templates')
                ->nullOnDelete();

            $table->uuid('email_template_id')->nullable();
            $table->foreign('email_template_id')
                ->references('id')
                ->on('email_templates')
                ->nullOnDelete();

            $table->uuid('bulk_group_id')->nullable();

            $table->string('channel'); // email | whatsapp | sms
            $table->string('source_type')->nullable(); // 'manual', 'rule'

            $table->dateTime('scheduled_at');

            $table->string('status')->default('pending'); // pending | sent | failed | skipped | cancelled

            $table->timestamp('sent_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->boolean('include_pdf')->default(false);

            $table->timestamps();

            // Indexes for better query performance
            $table->index(['status', 'scheduled_at']);
            $table->index(['invoice_id', 'status']);
            $table->index('reminder_rule_id');
            $table->index('reminder_rule_step_id');
            $table->index('bulk_group_id');
            $table->index(['bulk_group_id', 'status']);
            $table->index('email_template_id');
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
