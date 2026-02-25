<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('driver_phone', 32)->nullable();
            $table->string('driver_name')->nullable();
            $table->date('inspection_date');
            $table->date('due_date');
            $table->string('status', 24)->default('pending'); // pending, submitted, approved, rejected
            $table->string('request_type', 24)->default('scheduled'); // scheduled, manual
            $table->decimal('odometer_reading', 12, 2)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->text('driver_notes')->nullable();
            $table->text('reviewer_notes')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable(); // future: company user id
            $table->timestamps();
            $table->index(['company_id', 'status']);
            $table->index(['vehicle_id', 'inspection_date']);
            $table->index(['company_id', 'due_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_inspections');
    }
};
