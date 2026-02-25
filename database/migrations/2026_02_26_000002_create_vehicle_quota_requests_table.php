<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_quota_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('requested_count')->default(1)->comment('Number of extra slots requested');
            $table->string('status', 20)->default('pending')->index(); // pending, approved, rejected
            $table->text('note')->nullable()->comment('Company reason for request');
            $table->text('admin_note')->nullable()->comment('Admin response');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_quota_requests');
    }
};
