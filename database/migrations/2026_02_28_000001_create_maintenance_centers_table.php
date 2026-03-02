<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_centers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone', 20)->unique();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('password')->nullable(); // Not used - OTP only
            $table->rememberToken();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('company_maintenance_center', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('maintenance_center_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['company_id', 'maintenance_center_id'], 'company_mc_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_maintenance_center');
        Schema::dropIfExists('maintenance_centers');
    }
};
