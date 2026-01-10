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
        Schema::table('email_templates', function (Blueprint $table): void {
            $table->boolean('is_active')->default(true)->after('is_default');
            $table->softDeletes();
            
            // Add unique constraint for name per user
            $table->unique(['user_id', 'name'], 'unique_template_name_per_user');
        });
    }
};
