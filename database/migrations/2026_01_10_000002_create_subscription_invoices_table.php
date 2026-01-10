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
        Schema::create('subscription_invoices', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();

            $table->uuid('subscription_id')->nullable();
            $table->foreign('subscription_id')->references('id')->on('subscriptions')->nullOnDelete();

            // Invoice details
            $table->string('invoice_number', 50)->unique();
            $table->string('status', 50); // draft, pending, paid, failed, refunded

            // Amounts
            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->string('currency', 3)->default('USD');

            // Billing period
            $table->date('period_start');
            $table->date('period_end');

            // Dates
            $table->date('due_date');
            $table->timestamp('paid_at')->nullable();

            // Payment gateway
            $table->string('stripe_invoice_id', 100)->nullable();
            $table->string('paddle_invoice_id', 100)->nullable();

            // PDF storage
            $table->string('pdf_path')->nullable();

            $table->timestamps();

            $table->index('user_id');
            $table->index('subscription_id');
            $table->index('status');
            $table->index('due_date');
            $table->index('invoice_number');
        });
    }
};
