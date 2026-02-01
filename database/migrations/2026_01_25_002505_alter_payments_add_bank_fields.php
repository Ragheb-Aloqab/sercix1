<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // 1) method: أضف bank
            // ملاحظة: تعديل enum يختلف حسب DB
            // MySQL:
            $table->enum('method', ['tap', 'cash', 'bank'])->change();

            // 2) Bank transfer fields
            $table->foreignId('bank_account_id')
                ->nullable();

            $table->string('sender_name')->nullable()->after('amount');
            $table->string('receipt_path')->nullable()->after('sender_name');
            $table->text('note')->nullable()->after('receipt_path');

            $table->timestamp('reviewed_at')->nullable()->after('paid_at');
            $table->foreignId('reviewed_by')->nullable()->after('reviewed_at')
                ->constrained('users')
                ->nullOnDelete();

            $table->index(['method', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['method', 'status']);

            $table->dropConstrainedForeignId('bank_account_id');
            $table->dropColumn(['sender_name', 'receipt_path', 'note', 'reviewed_at']);
            $table->dropConstrainedForeignId('reviewed_by');

            // ارجع enum لو حاب (اختياري)
            $table->enum('method', ['tap', 'cash'])->change();
        });
    }
};
