<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Company-uploaded fuel invoices (image or PDF).
     */
    public function up(): void
    {
        Schema::create('company_fuel_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
            $table->decimal('amount', 12, 2)->nullable();
            $table->string('invoice_file');
            $table->string('file_type'); // 'image' or 'pdf'
            $table->string('original_filename')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_fuel_invoices');
    }
};
