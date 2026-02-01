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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'technician'])
                ->after('password');

            $table->enum('status', ['active', 'suspended'])
                ->default('active')
                ->after('role');

            $table->string('phone')->nullable()->after('email');

            $table->timestamp('last_login_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role',
                'status',
                'phone',
                'last_login_at',
            ]);
        });
    }
};
