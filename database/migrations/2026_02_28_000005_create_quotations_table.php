<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maintenance_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('maintenance_center_id')->constrained()->cascadeOnDelete();
            $table->decimal('price', 12, 2);
            $table->unsignedInteger('estimated_duration_minutes')->nullable();
            $table->text('notes')->nullable();
            $table->string('quotation_pdf_path')->nullable();
            $table->string('original_pdf_name')->nullable();
            $table->foreignId('submitted_by')->nullable()->constrained('maintenance_centers')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
            $table->unique(['maintenance_request_id', 'maintenance_center_id'], 'quot_mr_mc_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};
