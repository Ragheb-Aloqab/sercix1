<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('requested_by_name')->nullable()->after('notes');
            $table->string('driver_phone')->nullable()->after('requested_by_name');
        });

        // Add 'requested' to status enum (MySQL only)
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            \DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM(
                'pending','requested','accepted','on_the_way','in_progress','completed','cancelled','on_hold','paid'
            ) DEFAULT 'pending'");
        }
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['requested_by_name', 'driver_phone']);
        });
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            \DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM(
                'pending','accepted','on_the_way','in_progress','completed','cancelled','on_hold','paid'
            ) DEFAULT 'pending'");
        }
    }
};
