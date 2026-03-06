<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Seed common maintenance services used in invoice add form.
     */
    public function up(): void
    {
        $services = [
            'Oil Change',
            'Battery Replacement',
            'Spark Plug Replacement',
            'Tire Replacement',
            'Engine Maintenance',
        ];

        foreach ($services as $name) {
            DB::table('services')->insertOrIgnore([
                'name' => $name,
                'description' => null,
                'base_price' => 0,
                'duration_minutes' => 30,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('services')->whereIn('name', [
            'Oil Change',
            'Battery Replacement',
            'Spark Plug Replacement',
            'Tire Replacement',
            'Engine Maintenance',
        ])->delete();
    }
};
