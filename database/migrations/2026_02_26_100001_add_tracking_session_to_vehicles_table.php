<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->boolean('is_tracking_active')->default(false)->after('tracking_source');
            $table->string('tracking_driver_phone', 20)->nullable()->after('is_tracking_active');
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn(['is_tracking_active', 'tracking_driver_phone']);
        });
    }
};
