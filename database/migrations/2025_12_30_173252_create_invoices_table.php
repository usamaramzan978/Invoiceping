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
        Schema::create('invoices', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->uuid('business_id');
            $table->foreign('business_id')->references('id')->on('business_profiles')->cascadeOnDelete();

            $table->uuid('client_id');
            $table->foreign('client_id')->references('id')->on('clients')->cascadeOnDelete();

            $table->string('invoice_number', 50)->unique();
            $table->date('issue_date');
            $table->date('due_date');

            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->string('discount_type')->nullable(); // 'fixed' or 'percentage'
            $table->decimal('tax_amount', 12, 2)->default(0);

            $table->decimal('total_amount', 12, 2); // final total after discount & tax
            $table->string('currency', 10)->nullable(); // PKR , USD, EUR

            $table->string('status')->default('draft'); //  ['draft', 'sent', 'paid', 'overdue']

            $table->string('public_token', 64)->unique();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('sent_at')->nullable();

            $table->longText('notes')->nullable();
            $table->string('pdf_path')->nullable();

            $table->timestamps();

            $table->index(['business_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
