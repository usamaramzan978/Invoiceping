<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reminder_rule_step_templates', function (Blueprint $table): void {
            // Drop existing foreign key
            $table->dropForeign(['message_template_id']);

            // Make message_template_id nullable (for WhatsApp/SMS)
            $table->uuid('message_template_id')->nullable()->change();

            // Add email_template_id for email templates (separate table)
            $table->unsignedBigInteger('email_template_id')->nullable()->after('message_template_id');
            $table->foreign('email_template_id')
                ->references('id')
                ->on('email_templates')
                ->nullOnDelete();

            // Re-add foreign key for message_template_id
            $table->foreign('message_template_id')
                ->references('id')
                ->on('message_templates')
                ->nullOnDelete();
        });

        // Update channel comment to include SMS
        DB::statement("ALTER TABLE reminder_rule_step_templates MODIFY channel VARCHAR(255) COMMENT 'email | whatsapp | sms'");
    }
};
