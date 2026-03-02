<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_request_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maintenance_request_id')->constrained()->cascadeOnDelete();
            $table->string('from_status', 50)->nullable();
            $table->string('to_status', 50);
            $table->string('note')->nullable();
            $table->string('actor_type', 50)->nullable(); // company, driver, maintenance_center
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->timestamps();
            $table->index(['maintenance_request_id', 'created_at'], 'mr_status_logs_mr_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_request_status_logs');
    }
};
