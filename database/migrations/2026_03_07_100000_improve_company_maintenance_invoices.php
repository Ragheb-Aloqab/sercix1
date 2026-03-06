<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * - Make invoice_file optional (nullable)
     * - Create pivot table for invoice-services (many-to-many)
     */
    public function up(): void
    {
        Schema::table('company_maintenance_invoices', function (Blueprint $table) {
            $table->string('invoice_file')->nullable()->change();
            $table->string('file_type')->nullable()->change();
        });

        Schema::create('company_maintenance_invoice_service', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_maintenance_invoice_id');
            $table->unsignedBigInteger('service_id');
            $table->timestamps();

            $table->foreign('company_maintenance_invoice_id', 'cmi_invoice_id_fk')
                ->references('id')->on('company_maintenance_invoices')->cascadeOnDelete();
            $table->foreign('service_id', 'cmi_service_id_fk')
                ->references('id')->on('services')->cascadeOnDelete();
            $table->unique(['company_maintenance_invoice_id', 'service_id'], 'cmi_invoice_service_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_maintenance_invoice_service');

        Schema::table('company_maintenance_invoices', function (Blueprint $table) {
            $table->string('invoice_file')->nullable(false)->change();
            $table->string('file_type')->nullable(false)->change();
        });
    }
};
