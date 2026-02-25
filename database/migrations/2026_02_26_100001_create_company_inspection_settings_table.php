<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_inspection_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_enabled')->default(false);
            $table->string('frequency_type', 32)->default('monthly'); // monthly, every_x_days, manual
            $table->unsignedInteger('frequency_days')->nullable(); // for every_x_days
            $table->unsignedInteger('deadline_days')->default(3); // days to upload after request
            $table->boolean('block_if_overdue')->default(false);
            $table->timestamps();
            $table->unique('company_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_inspection_settings');
    }
};
