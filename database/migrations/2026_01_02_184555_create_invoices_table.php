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
        /*Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')->unique()->constrained('orders')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();

            $table->string('invoice_number')->unique();

            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);

            $table->enum('status', ['unpaid', 'paid', 'void'])->default('unpaid')->index();

            $table->string('pdf_path')->nullable();

            $table->timestamps();

            $table->index(['company_id', 'status']);
        });*/
        Schema::create('invoices', function (Blueprint $table) {
    $table->id();

    $table->foreignId('order_id')->unique()->constrained('orders')->cascadeOnDelete();
    $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();

    $table->string('invoice_number')->unique();

    $table->decimal('subtotal', 10, 2)->default(0);
    $table->decimal('tax', 10, 2)->default(0);
    $table->decimal('paid_amount', 10, 2)->default(0);

    $table->enum('status', ['unpaid', 'partial', 'paid', 'void'])
          ->default('unpaid')
          ->index();

    $table->string('pdf_path')->nullable();

    $table->timestamps();

    $table->index(['company_id', 'status']);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
