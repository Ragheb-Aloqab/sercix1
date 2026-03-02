<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->string('maintenance_type', 50)->index(); // periodic, emergency, inspection, parts
            $table->string('description', 500);
            $table->string('status', 50)->default('new_request')->index();
            $table->string('rejection_reason')->nullable();
            $table->string('requested_by_name')->nullable();
            $table->string('driver_phone', 20)->nullable();
            $table->string('city')->nullable();
            $table->text('address')->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('approved_center_id')->nullable()->constrained('maintenance_centers')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('companies')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('invoice_approved_by')->nullable()->constrained('companies')->nullOnDelete();
            $table->timestamp('invoice_approved_at')->nullable();
            $table->string('final_invoice_pdf_path')->nullable();
            $table->string('final_invoice_original_name')->nullable();
            $table->timestamp('final_invoice_uploaded_at')->nullable();
            $table->date('completion_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->index(['company_id', 'status']);
            $table->index(['driver_phone', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_requests');
    }
};
