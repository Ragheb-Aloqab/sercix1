<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_inspection_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_inspection_id')->constrained()->cascadeOnDelete();
            $table->string('photo_type', 32); // front, rear, left_side, right_side, interior, odometer, other
            $table->string('file_path');
            $table->string('original_name')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->index(['vehicle_inspection_id', 'photo_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_inspection_photos');
    }
};
