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
        Schema::create('fuel_refills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();

            // Core refill data
            $table->decimal('liters', 10, 2);           // Amount of fuel (L)
            $table->decimal('cost', 12, 2);             // Total cost (SAR)
            $table->decimal('price_per_liter', 8, 2)->nullable(); // Optional, for analytics
            $table->timestamp('refilled_at')->index();  // When refill occurred
            $table->unsignedInteger('odometer_km')->nullable();   // Odometer reading (for consumption calc)
            $table->string('fuel_type', 20)->nullable()->default('petrol'); // petrol, diesel, etc.
            $table->text('notes')->nullable();

            // Future API integration
            $table->string('provider', 50)->default('manual')->index(); // manual, api_xyz, etc.
            $table->string('external_id', 100)->nullable()->index();    // ID from external provider
            $table->json('external_metadata')->nullable();              // Extra data from API

            // Receipt / proof
            $table->string('receipt_path')->nullable();

            // Audit: who logged it (driver phone for driver session, or user_id)
            $table->string('logged_by_phone')->nullable()->index();
            $table->foreignId('logged_by_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['vehicle_id', 'refilled_at']);
            $table->index(['company_id', 'refilled_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fuel_refills');
    }
};
