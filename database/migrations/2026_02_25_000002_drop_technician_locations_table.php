<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('technician_locations');
    }

    public function down(): void
    {
        Schema::create('technician_locations', function ($table) {
            $table->id();
            $table->foreignId('technician_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);
            $table->timestamp('recorded_at')->index();
            $table->timestamps();
            $table->index(['technician_id', 'recorded_at']);
        });
    }
};
