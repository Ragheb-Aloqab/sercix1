<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add service type category for maintenance invoices (maintenance, oil_change, painting, tires, other).
     */
    public function up(): void
    {
        Schema::table('company_maintenance_invoices', function (Blueprint $table) {
            $table->string('service_type', 30)->nullable()->after('vehicle_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_maintenance_invoices', function (Blueprint $table) {
            $table->dropColumn('service_type');
        });
    }
};
