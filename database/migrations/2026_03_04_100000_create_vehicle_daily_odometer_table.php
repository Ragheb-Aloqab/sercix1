<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_daily_odometer', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->date('date')->comment('Date for which the last odometer is stored');
            $table->decimal('odometer_km', 12, 2)->comment('Last odometer reading for that day');
            $table->string('source', 32)->nullable()->comment('vehicle_locations, fuel_refills, mobile_tracking_trips');
            $table->timestamps();

            $table->unique(['vehicle_id', 'date']);
            $table->index(['vehicle_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_daily_odometer');
    }
};
