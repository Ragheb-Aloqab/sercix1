<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds invoice_file and file_type to invoices table.
     * Adds file_type to maintenance_requests for display (image vs pdf).
     * Creates company_maintenance_invoices for company-uploaded standalone invoices.
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('invoice_file')->nullable()->after('pdf_path');
            $table->string('file_type')->nullable()->after('invoice_file'); // 'image' or 'pdf'
        });

        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->string('file_type')->nullable()->after('final_invoice_original_name'); // 'image' or 'pdf'
        });

        Schema::create('company_maintenance_invoices', function (Blueprint $table) {
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
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['invoice_file', 'file_type']);
        });

        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->dropColumn('file_type');
        });

        Schema::dropIfExists('company_maintenance_invoices');
    }
};
