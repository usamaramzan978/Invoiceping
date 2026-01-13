<?php

declare(strict_types=1);

use App\Enums\WhatsAppProviderType;
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
        Schema::create('whatsapp_providers', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();

            $table->string('name'); // Friendly name for the provider
            $table->string('type')->default(WhatsAppProviderType::WHATSAPP_CLOUD_API->value)->index();

            // WhatsApp Cloud API credentials
            $table->text('access_token')->nullable();
            $table->string('phone_number_id')->nullable();
            $table->string('business_account_id')->nullable();
            $table->string('app_id')->nullable();
            $table->text('app_secret')->nullable();

            // Twilio credentials
            $table->string('account_sid')->nullable();
            $table->text('auth_token')->nullable();
            $table->string('from_phone_number')->nullable();
            $table->string('whatsapp_sandbox_number')->nullable();

            // Vonage credentials
            $table->string('api_key')->nullable();
            $table->text('api_secret')->nullable();
            $table->string('from_number')->nullable();
            $table->string('application_id')->nullable();

            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_default')->default(false)->index();
            $table->text('notes')->nullable(); // Optional notes about the provider

            $table->timestamp('last_used_at')->nullable()->index();

            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->index(['user_id', 'type']);
            $table->index(['user_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_providers');
    }
};
