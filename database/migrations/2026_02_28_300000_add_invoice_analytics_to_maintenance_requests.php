<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->decimal('approved_quote_amount', 12, 2)->nullable()->after('approved_quotation_id');
            $table->decimal('final_invoice_amount', 12, 2)->nullable()->after('final_invoice_original_name');
        });

    }

    public function down(): void
    {
        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->dropColumn(['approved_quote_amount', 'final_invoice_amount']);
        });
    }
};
