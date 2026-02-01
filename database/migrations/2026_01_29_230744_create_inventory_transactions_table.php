<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();

            // ربط مع جدول الأصناف inventory_items
            $table->foreignId('inventory_item_id')
                  ->constrained('inventory_items')
                  ->cascadeOnDelete();

            // نوع الحركة
            $table->enum('transaction_type', [
                'in',
                'out',
                'adjustment',
                'return',
                'transfer'
            ]);

            // التغير في الكمية (قد يكون سالب أو موجب)
            $table->integer('quantity_change');

            // الكمية الجديدة بعد الحركة
            $table->integer('new_quantity');

            // سعر الوحدة وقت الحركة
            $table->decimal('unit_price', 10, 2)->nullable();

            // نوع الطلب المرتبط (اختياري)
            $table->string('related_order_type')->nullable();

            // رقم مرجعي خارجي (فاتورة - سند - الخ)
            $table->string('reference_number')->nullable();

            $table->text('notes')->nullable();

            // ربط بجدول الطلبات
            $table->foreignId('related_order_id')
                  ->nullable()
                  ->constrained('orders')
                  ->nullOnDelete();

            // من قام بالحركة
            $table->foreignId('created_by')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
    }
};