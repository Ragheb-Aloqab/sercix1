<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->decimal('fuel_balance', 12, 2)->default(0)->after('is_active');
        });

        Schema::create('fuel_payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('payment_method', 50)->default('bank_transfer');
            $table->string('receipt_path')->nullable();
            $table->string('receipt_path_original')->nullable();
            $table->string('reference_number')->nullable();
            $table->timestamps();
            $table->index(['company_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn('fuel_balance');
        });
        Schema::dropIfExists('fuel_payment_transactions');
    }
};
