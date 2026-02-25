<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicle_locations', function (Blueprint $table) {
            $table->string('source', 20)->default('device_api')->after('vehicle_id');
            $table->string('driver_phone', 30)->nullable()->after('source');
        });
    }

    public function down(): void
    {
        Schema::table('vehicle_locations', function (Blueprint $table) {
            $table->dropColumn(['source', 'driver_phone']);
        });
    }
};
