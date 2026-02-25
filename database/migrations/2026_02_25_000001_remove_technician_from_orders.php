<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['technician_id']);
            $table->dropIndex(['technician_id', 'status']);
            $table->dropColumn('technician_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('technician_id')->nullable()->after('vehicle_id')->constrained('users')->nullOnDelete();
            $table->index(['technician_id', 'status']);
        });
    }
};
