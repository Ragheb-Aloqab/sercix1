<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->text('tracking_api_key')->nullable()->after('status');
            $table->string('tracking_base_url', 500)->nullable()->after('tracking_api_key');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['tracking_api_key', 'tracking_base_url']);
        });
    }
};
