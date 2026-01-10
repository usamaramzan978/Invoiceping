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
        Schema::create('billing_transactions', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();

            $table->uuid('subscription_id')->nullable();
            $table->foreign('subscription_id')->references('id')->on('subscriptions')->nullOnDelete();

            // Transaction details
            $table->string('transaction_id', 100)->unique(); // Payment gateway transaction ID
            $table->string('type', 50); // payment, refund, adjustment
            $table->string('status', 50); // pending, completed, failed, refunded

            // Amounts
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('USD');

            // Payment method
            $table->string('payment_method', 50)->nullable(); // card, paypal, bank_transfer
            $table->string('payment_gateway', 50)->nullable(); // stripe, paddle

            // Metadata
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->text('failure_reason')->nullable();

            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('subscription_id');
            $table->index('status');
            $table->index('type');
            $table->index('processed_at');
        });
    }
};
