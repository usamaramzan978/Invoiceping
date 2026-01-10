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
        Schema::create('subscription_plans', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->text('description')->nullable();

            // Pricing
            $table->decimal('monthly_price', 10, 2);
            $table->decimal('yearly_price', 10, 2);
            $table->string('currency', 3)->default('USD');

            // Features
            $table->integer('invoice_limit')->nullable(); // null = unlimited
            $table->integer('client_limit')->nullable();
            $table->boolean('whatsapp_enabled')->default(true);
            $table->boolean('custom_message')->default(false);
            $table->boolean('email_support')->default(false);
            $table->boolean('priority_support')->default(false);
            $table->boolean('api_access')->default(false);

            // Plan management
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);

            $table->timestamps();

            $table->index('slug');
            $table->index('is_active');
        });
    }
};
