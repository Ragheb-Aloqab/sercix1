<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Make cost and liters optional for driver fuel refills (e.g. log refill without receipt or total).
     */
    public function up(): void
    {
        Schema::table('fuel_refills', function (Blueprint $table) {
            $table->decimal('liters', 10, 2)->nullable()->change();
            $table->decimal('cost', 12, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('fuel_refills', function (Blueprint $table) {
            $table->decimal('liters', 10, 2)->nullable(false)->change();
            $table->decimal('cost', 12, 2)->nullable(false)->change();
        });
    }
};
