<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('notifications')) {
            return;
        }

        Schema::table('notifications', function (Blueprint $blueprint) {
            $blueprint->index('read_at', 'notifications_read_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $blueprint) {
            $blueprint->dropIndex('notifications_read_at_index');
        });
    }
};
