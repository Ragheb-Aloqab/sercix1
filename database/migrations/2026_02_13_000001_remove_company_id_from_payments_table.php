<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Payments should only connect to orders; company is accessed via order->company.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
        });
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['company_id', 'status']);
            $table->dropColumn('company_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('company_id')->after('order_id')->nullable()->constrained('companies')->cascadeOnDelete();
        });
        \DB::statement('UPDATE payments p JOIN orders o ON p.order_id = o.id SET p.company_id = o.company_id');
        Schema::table('payments', function (Blueprint $table) {
            $table->index(['company_id', 'status']);
        });
    }
};
