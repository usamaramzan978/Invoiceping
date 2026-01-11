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
        Schema::table('reminder_schedules', function (Blueprint $table): void {
            // Make message_template_id nullable (it's only for WhatsApp/SMS)
            $table->uuid('message_template_id')->nullable()->change();

            // Add email_template_id for email templates
            $table->unsignedBigInteger('email_template_id')->nullable()->after('message_template_id');
            $table->foreign('email_template_id')
                ->references('id')
                ->on('email_templates')
                ->nullOnDelete();

            // Add index for better query performance
            $table->index('email_template_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reminder_schedules', function (Blueprint $table): void {
            $table->dropForeign(['email_template_id']);
            $table->dropIndex(['email_template_id']);
            $table->dropColumn('email_template_id');
        });
    }
};
