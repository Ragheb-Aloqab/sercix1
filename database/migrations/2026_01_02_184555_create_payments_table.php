<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();

            $table->enum('method', ['tap', 'cash'])->index();
            $table->enum('status', ['pending', 'paid', 'failed', 'refunded'])->default('pending')->index();

            $table->decimal('amount', 10, 2)->default(0);

            // Tap fields (store what you need)
            $table->string('tap_charge_id')->nullable()->index();
            $table->string('tap_reference')->nullable();
            $table->json('tap_payload')->nullable();

            $table->timestamp('paid_at')->nullable();

            $table->timestamps();

            $table->index(['company_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
