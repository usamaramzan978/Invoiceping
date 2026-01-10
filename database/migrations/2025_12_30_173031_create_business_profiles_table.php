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
        Schema::create('business_profiles', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();

            $table->string('business_name', 200);
            $table->string('tax_id', 50)->nullable(); // VAT / GST / NTN
            $table->string('whatsapp_number', 20)->nullable();
            $table->string('email', 150)->nullable();
            $table->text('address')->nullable();

            $table->string('image')->nullable();

            $table->string('currency', 10)->nullable();
            $table->string('timezone', 50)->nullable();

            // Reminder Automation
            $table->uuid('default_reminder_rule_id')->nullable();
            $table->boolean('auto_apply_reminders')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_profiles');
    }
};
