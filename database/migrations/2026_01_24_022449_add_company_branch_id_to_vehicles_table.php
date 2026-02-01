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
        Schema::table('vehicles', function (Blueprint $table) {
            // إضافة عمود الفرع
            $table->foreignId('company_branch_id')
                ->nullable()
                ->after('company_id') // اختياري: ترتيب العمود
                ->constrained('company_branches')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            // حذف المفتاح الأجنبي أولاً
            $table->dropForeign(['company_branch_id']);
            // ثم حذف العمود
            $table->dropColumn('company_branch_id');
        });
    }
};
