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
        Schema::create('subscriptions', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();

            $table->uuid('plan_id');
            $table->foreign('plan_id')->references('id')->on('subscription_plans')->restrictOnDelete();

            // Payment gateway integration
            $table->string('stripe_subscription_id', 100)->nullable();
            $table->string('paddle_subscription_id', 100)->nullable();

            // Billing cycle
            $table->enum('billing_cycle', ['monthly', 'yearly'])->default('monthly');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('USD');

            // Status tracking
            $table->string('status', 20)->default('active'); // active, trialing, canceled, expired, past_due

            // Date tracking
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('renews_at')->nullable();
            $table->timestamp('canceled_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
            $table->index('status');
            $table->index('renews_at');
        });
    }
};
