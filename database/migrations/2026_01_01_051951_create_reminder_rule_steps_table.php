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
        Schema::create('reminder_rule_steps', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->uuid('reminder_rule_id');
            $table->foreign('reminder_rule_id')
                ->references('id')
                ->on('reminder_rules')
                ->cascadeOnDelete();

            $table->string('reminder_type');
            // before_due | on_due | after_due

            $table->integer('offset_days')->default(0);
            // before_due = -N
            // on_due = 0
            // after_due = +N

            $table->integer('sort_order')->default(0);

            $table->timestamps();

            $table->index(['reminder_rule_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reminder_rule_steps');
    }
};
