<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Make invoice_file and file_type optional for company fuel invoices.
     */
    public function up(): void
    {
        Schema::table('company_fuel_invoices', function (Blueprint $table) {
            $table->string('invoice_file')->nullable()->change();
            $table->string('file_type')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_fuel_invoices', function (Blueprint $table) {
            $table->string('invoice_file')->nullable(false)->change();
            $table->string('file_type')->nullable(false)->change();
        });
    }
};
