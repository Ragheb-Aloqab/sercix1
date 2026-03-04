<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mobile_tracking_trips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->string('driver_phone', 30)->nullable();
            $table->decimal('start_odometer', 12, 2)->comment('Odometer value when driver started tracking');
            $table->decimal('end_odometer', 12, 2)->comment('Odometer value when driver stopped tracking');
            $table->decimal('trip_distance_km', 12, 2)->default(0)->comment('end_odometer - start_odometer');
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();

            $table->index(['vehicle_id', 'started_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_tracking_trips');
    }
};
