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
        Schema::create('clients', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->uuid('business_id');
            $table->foreign('business_id')->references('id')->on('business_profiles')->cascadeOnDelete();

            $table->string('name', 150);
            $table->string('whatsapp_number', 20);
            $table->string('email', 150)->nullable();
            $table->string('status')->nullable(); // ['active', 'inactive']
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
