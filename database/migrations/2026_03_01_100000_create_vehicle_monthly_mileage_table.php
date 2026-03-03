<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_monthly_mileage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->unsignedTinyInteger('month'); // 1-12
            $table->unsignedSmallInteger('year');
            $table->decimal('start_odometer', 12, 2)->nullable()->comment('Odometer at month start (snapshot)');
            $table->decimal('end_odometer', 12, 2)->nullable()->comment('Odometer at month end (locked when month closes)');
            $table->decimal('total_km', 12, 2)->default(0)->comment('total_km = end_odometer - start_odometer (locked for closed months)');
            $table->boolean('odometer_reset_detected')->default(false)->comment('True if odometer decreased (device replaced/reset)');
            $table->boolean('is_closed')->default(false)->comment('True when month ended, data is read-only');
            $table->timestamps();

            $table->unique(['vehicle_id', 'month', 'year']);
            $table->index(['vehicle_id', 'year', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_monthly_mileage');
    }
};
