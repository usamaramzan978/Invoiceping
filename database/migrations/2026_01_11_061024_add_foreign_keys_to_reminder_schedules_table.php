<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('reminder_schedules', function (Blueprint $table): void {
            // Add foreign key for reminder_rule_id (if not exists)
            if (! $this->foreignKeyExists('reminder_schedules', 'reminder_rule_id')) {
                $table->foreign('reminder_rule_id')
                    ->references('id')
                    ->on('reminder_rules')
                    ->nullOnDelete();
            }

            // Add foreign key for reminder_rule_step_id (if not exists)
            if (! $this->foreignKeyExists('reminder_schedules', 'reminder_rule_step_id')) {
                $table->foreign('reminder_rule_step_id')
                    ->references('id')
                    ->on('reminder_rule_steps')
                    ->nullOnDelete();
            }

            // Add foreign key for message_template_id (if not exists)
            if (! $this->foreignKeyExists('reminder_schedules', 'message_template_id')) {
                $table->foreign('message_template_id')
                    ->references('id')
                    ->on('message_templates')
                    ->nullOnDelete();
            }

            // Add indexes for better query performance (if not exists)
            if (! $this->indexExists('reminder_schedules', 'reminder_schedules_reminder_rule_id_index')) {
                $table->index('reminder_rule_id');
            }

            if (! $this->indexExists('reminder_schedules', 'reminder_schedules_reminder_rule_step_id_index')) {
                $table->index('reminder_rule_step_id');
            }

            if (! $this->indexExists('reminder_schedules', 'reminder_schedules_bulk_group_id_index')) {
                $table->index('bulk_group_id');
            }

            if (! $this->indexExists('reminder_schedules', 'reminder_schedules_bulk_group_id_status_index')) {
                $table->index(['bulk_group_id', 'status']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reminder_schedules', function (Blueprint $table): void {
            // Drop foreign keys
            if ($this->foreignKeyExists('reminder_schedules', 'reminder_rule_id')) {
                $table->dropForeign(['reminder_rule_id']);
            }

            if ($this->foreignKeyExists('reminder_schedules', 'reminder_rule_step_id')) {
                $table->dropForeign(['reminder_rule_step_id']);
            }

            if ($this->foreignKeyExists('reminder_schedules', 'message_template_id')) {
                $table->dropForeign(['message_template_id']);
            }

            // Drop indexes
            if ($this->indexExists('reminder_schedules', 'reminder_schedules_reminder_rule_id_index')) {
                $table->dropIndex(['reminder_rule_id']);
            }

            if ($this->indexExists('reminder_schedules', 'reminder_schedules_reminder_rule_step_id_index')) {
                $table->dropIndex(['reminder_rule_step_id']);
            }

            if ($this->indexExists('reminder_schedules', 'reminder_schedules_bulk_group_id_index')) {
                $table->dropIndex(['bulk_group_id']);
            }

            if ($this->indexExists('reminder_schedules', 'reminder_schedules_bulk_group_id_status_index')) {
                $table->dropIndex(['bulk_group_id', 'status']);
            }
        });
    }

    /**
     * Check if a foreign key exists.
     */
    private function foreignKeyExists(string $table, string $column): bool
    {
        $connection = DB::connection();
        $database = $connection->getDatabaseName();

        $result = DB::select(
            "SELECT CONSTRAINT_NAME 
             FROM information_schema.KEY_COLUMN_USAGE 
             WHERE TABLE_SCHEMA = ? 
             AND TABLE_NAME = ? 
             AND COLUMN_NAME = ?
             AND CONSTRAINT_NAME != 'PRIMARY'",
            [$database, $table, $column]
        );

        return count($result) > 0;
    }

    /**
     * Check if an index exists.
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $connection = DB::connection();
        $database = $connection->getDatabaseName();

        $result = DB::select(
            'SELECT INDEX_NAME 
             FROM information_schema.STATISTICS 
             WHERE TABLE_SCHEMA = ? 
             AND TABLE_NAME = ? 
             AND INDEX_NAME = ?',
            [$database, $table, $indexName]
        );

        return count($result) > 0;
    }
};
