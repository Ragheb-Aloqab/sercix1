<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->decimal('lat', 10, 8);
            $table->decimal('lng', 11, 8);
            $table->decimal('speed', 8, 2)->nullable();
            $table->string('address')->nullable();
            $table->string('status')->nullable(); // moving, stopped, idle
            $table->decimal('odometer', 12, 2)->nullable();
            $table->decimal('engine_hours', 10, 2)->nullable();
            $table->decimal('fuel_level', 5, 2)->nullable();
            $table->json('raw_data')->nullable();
            $table->timestamp('tracker_timestamp')->nullable();
            $table->timestamps();

            $table->index(['vehicle_id', 'tracker_timestamp']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_locations');
    }
};
