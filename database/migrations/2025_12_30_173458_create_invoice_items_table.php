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
        Schema::create('invoice_items', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->uuid('invoice_id');
            $table->foreign('invoice_id')->references('id')->on('invoices')->cascadeOnDelete();

            $table->string('name');
            $table->string('description', 255)->nullable();
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 12, 2);

            $table->decimal('line_total', 12, 2);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
