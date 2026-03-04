<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'theme_preference')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('theme_preference', 20)->nullable()->after('status'); // light, dark, system
            });
        }

        if (Schema::hasTable('companies') && !Schema::hasColumn('companies', 'theme_preference')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->string('theme_preference', 20)->nullable()->after('status'); // light, dark, system
            });
        }

        if (Schema::hasTable('maintenance_centers') && !Schema::hasColumn('maintenance_centers', 'theme_preference')) {
            Schema::table('maintenance_centers', function (Blueprint $table) {
                $table->string('theme_preference', 20)->nullable()->after('status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'theme_preference')) {
            Schema::table('users', fn (Blueprint $t) => $t->dropColumn('theme_preference'));
        }
        if (Schema::hasColumn('companies', 'theme_preference')) {
            Schema::table('companies', fn (Blueprint $t) => $t->dropColumn('theme_preference'));
        }
        if (Schema::hasTable('maintenance_centers') && Schema::hasColumn('maintenance_centers', 'theme_preference')) {
            Schema::table('maintenance_centers', fn (Blueprint $t) => $t->dropColumn('theme_preference'));
        }
    }
};
