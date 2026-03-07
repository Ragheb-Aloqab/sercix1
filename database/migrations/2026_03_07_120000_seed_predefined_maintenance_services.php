<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Seed the full predefined maintenance services list for invoice creation.
     */
    public function up(): void
    {
        $services = [
            // Engine & Oil
            'Oil Change',
            'Oil Filter Replacement',
            'Air Filter Replacement',
            'Fuel Filter Replacement',
            'Spark Plug Replacement',
            'Engine Cleaning',
            // Electrical
            'Battery Replacement',
            'Alternator Repair',
            'Starter Motor Repair',
            'Electrical System Check',
            // Tires
            'Tire Replacement',
            'Tire Repair',
            'Tire Balancing',
            'Wheel Alignment',
            // Brakes
            'Brake Pad Replacement',
            'Brake Disc Replacement',
            'Brake Fluid Replacement',
            // Cooling
            'Radiator Repair',
            'Coolant Replacement',
            'Thermostat Replacement',
            // Transmission
            'Transmission Oil Change',
            'Clutch Repair',
            // AC
            'AC Gas Refill',
            'AC System Repair',
            // General
            'Car Wash',
            'Interior Cleaning',
            'Vehicle Inspection',
            'General Maintenance',
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
        // Services may be linked to invoices - do not delete on rollback
    }
};
