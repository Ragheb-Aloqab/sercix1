<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_tracking_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->string('driver_phone', 32);
            $table->boolean('is_active')->default(true);
            $table->timestamp('started_at');
            $table->timestamp('last_reported_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();

            $table->index(['driver_phone', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_tracking_sessions');
    }
};
