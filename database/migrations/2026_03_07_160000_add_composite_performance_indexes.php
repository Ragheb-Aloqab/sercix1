<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Composite indexes for list/filter/sort queries (Phase 1 — Optimization).
     * Single-column indexes remain in add_performance_indexes / table creates.
     */
    public function up(): void
    {
        $this->addCompositeIndex('vehicles', ['company_id', 'created_at'], 'vehicles_company_id_created_at_idx');
        // (company_id, is_active) already exists on vehicles from create_vehicles_table

        $this->addCompositeIndex('fuel_refills', ['vehicle_id', 'created_at'], 'fuel_refills_vehicle_id_created_at_idx');
        $this->addCompositeIndex('fuel_refills', ['company_id', 'created_at'], 'fuel_refills_company_id_created_at_idx');

        $this->addCompositeIndex('vehicle_locations', ['vehicle_id', 'created_at'], 'vehicle_locations_vehicle_id_created_at_idx');

        // (company_id, status) already exists on maintenance_requests
        $this->addCompositeIndex('maintenance_requests', ['vehicle_id', 'status'], 'maintenance_requests_vehicle_id_status_idx');

        $this->addCompositeIndex('orders', ['company_id', 'status', 'created_at'], 'orders_company_id_status_created_at_idx');

        // (vehicle_id, date) already exists on vehicle_daily_odometer
    }

    public function down(): void
    {
        $this->dropCompositeIndex('vehicles', 'vehicles_company_id_created_at_idx');
        $this->dropCompositeIndex('fuel_refills', 'fuel_refills_vehicle_id_created_at_idx');
        $this->dropCompositeIndex('fuel_refills', 'fuel_refills_company_id_created_at_idx');
        $this->dropCompositeIndex('vehicle_locations', 'vehicle_locations_vehicle_id_created_at_idx');
        $this->dropCompositeIndex('maintenance_requests', 'maintenance_requests_vehicle_id_status_idx');
        $this->dropCompositeIndex('orders', 'orders_company_id_status_created_at_idx');
    }

    private function addCompositeIndex(string $table, array $columns, string $indexName): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }
        foreach ($columns as $col) {
            if (!Schema::hasColumn($table, $col)) {
                return;
            }
        }
        try {
            Schema::table($table, fn (Blueprint $t) => $t->index($columns, $indexName));
        } catch (\Throwable $e) {
            // Index may already exist (e.g. from table create)
        }
    }

    private function dropCompositeIndex(string $table, string $indexName): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }
        try {
            Schema::table($table, fn (Blueprint $t) => $t->dropIndex($indexName));
        } catch (\Throwable $e) {
            //
        }
    }
};
