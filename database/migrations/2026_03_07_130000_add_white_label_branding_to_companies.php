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
        Schema::table('companies', function (Blueprint $table) {
            if (!Schema::hasColumn('companies', 'subdomain')) {
                $table->string('subdomain')->nullable()->unique()->after('company_name');
            }
            if (!Schema::hasColumn('companies', 'primary_color')) {
                $table->string('primary_color', 20)->nullable()->after('subdomain');
            }
            if (!Schema::hasColumn('companies', 'secondary_color')) {
                $table->string('secondary_color', 20)->nullable()->after('primary_color');
            }
            if (!Schema::hasColumn('companies', 'logo')) {
                $table->string('logo')->nullable()->after('secondary_color');
            }
            if (!Schema::hasColumn('companies', 'white_label_enabled')) {
                $table->boolean('white_label_enabled')->default(false)->after('logo');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['subdomain', 'primary_color', 'secondary_color', 'logo', 'white_label_enabled']);
        });
    }
};
