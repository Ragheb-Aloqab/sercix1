<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add support for fuel invoices: make order_id nullable, add fuel_refill_id and invoice_type.
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->dropUnique(['order_id']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('order_id')->nullable()->change();
            $table->foreignId('fuel_refill_id')->nullable()->after('order_id')
                ->constrained('fuel_refills')->nullOnDelete();
            $table->string('invoice_type', 20)->default('service')->after('company_id');
            $table->foreign('order_id')->references('id')->on('orders')->nullOnDelete();
            $table->unique('order_id');
            $table->unique('fuel_refill_id');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropUnique(['fuel_refill_id']);
            $table->dropUnique(['order_id']);
            $table->dropForeign(['fuel_refill_id']);
            $table->dropForeign(['order_id']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('order_id')->nullable(false)->change();
            $table->dropColumn(['fuel_refill_id', 'invoice_type']);
            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
            $table->unique('order_id');
        });
    }
};
