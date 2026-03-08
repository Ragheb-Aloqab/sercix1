<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_request_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maintenance_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->foreignId('driver_proposed_service_id')->nullable()->constrained('driver_proposed_services')->nullOnDelete();
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['maintenance_request_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_request_services');
    }
};
