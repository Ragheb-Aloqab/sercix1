<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotation_line_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('maintenance_request_service_id')->constrained()->cascadeOnDelete();
            $table->decimal('price', 12, 2);
            $table->string('image_path')->nullable();
            $table->string('original_image_name')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['quotation_id', 'maintenance_request_service_id'], 'quotation_line_quotation_mr_service_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotation_line_items');
    }
};
