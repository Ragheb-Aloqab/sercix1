<?php

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
        Schema::create('company_services', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();

            // per-company pricing/time (optional but useful)
            $table->decimal('base_price', 10, 2)->nullable();
            $table->unsignedInteger('estimated_minutes')->nullable();

            $table->boolean('is_enabled')->default(true)->index();

            $table->timestamps();

            $table->unique(['company_id', 'service_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_services');
    }
};
