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
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
             $table->foreignId('service_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('name');
            $table->string('sku')->nullable()->unique();
            $table->string('category')->nullable(); // زيت/فلتر/قطعة...
            $table->decimal('unit_price', 10, 2)->default(0);

            $table->integer('qty')->default(0);
            $table->integer('min_qty')->default(0);

            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
