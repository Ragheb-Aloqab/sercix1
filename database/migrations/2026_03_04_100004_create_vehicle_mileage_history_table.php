<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_mileage_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->string('tracking_type', 16)->comment('gps or manual');
            $table->decimal('previous_reading', 12, 2)->nullable()->comment('Previous odometer value');
            $table->decimal('current_reading', 12, 2)->comment('Current odometer value');
            $table->decimal('calculated_difference', 12, 2)->default(0)->comment('current - previous');
            $table->date('recorded_date')->comment('Date of the reading');
            $table->string('source', 64)->nullable()->comment('vehicle_locations, fuel_refills, mobile_tracking_trips, manual_daily');
            $table->timestamps();

            $table->index(['vehicle_id', 'recorded_date']);
            $table->index(['vehicle_id', 'tracking_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_mileage_history');
    }
};
