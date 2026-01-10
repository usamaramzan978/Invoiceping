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
        Schema::create('reminder_rule_step_templates', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->uuid('reminder_rule_step_id');
            $table->foreign('reminder_rule_step_id')
                ->references('id')
                ->on('reminder_rule_steps')
                ->cascadeOnDelete();

            $table->uuid('message_template_id');
            $table->foreign('message_template_id')
                ->references('id')
                ->on('message_templates')
                ->cascadeOnDelete();

            $table->string('channel'); // email | whatsapp

            $table->timestamps();

            $table->unique(
                ['reminder_rule_step_id', 'channel'],
                'rule_step_channel_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reminder_rule_step_templates');
    }
};
