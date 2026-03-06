<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add tax option support for maintenance invoices.
     */
    public function up(): void
    {
        Schema::table('company_maintenance_invoices', function (Blueprint $table) {
            $table->decimal('original_amount', 12, 2)->nullable()->after('amount');
            $table->decimal('vat_amount', 12, 2)->nullable()->after('original_amount');
            $table->string('tax_type', 20)->nullable()->after('vat_amount'); // 'with_tax' | 'without_tax'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_maintenance_invoices', function (Blueprint $table) {
            $table->dropColumn(['original_amount', 'vat_amount', 'tax_type']);
        });
    }
};
