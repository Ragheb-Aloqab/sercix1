<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * One image for the whole quotation, shown at the end of the invoice (no per-service images).
     */
    public function up(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->string('invoice_image_path')->nullable()->after('original_pdf_name');
            $table->string('invoice_image_original_name')->nullable()->after('invoice_image_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn(['invoice_image_path', 'invoice_image_original_name']);
        });
    }
};
