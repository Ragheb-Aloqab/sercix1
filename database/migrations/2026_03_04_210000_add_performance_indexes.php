<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addIndexIfNotExists('orders', 'company_id');
        $this->addIndexIfNotExists('orders', 'vehicle_id');
        $this->addIndexIfNotExists('orders', 'created_at');
        $this->addIndexIfNotExists('invoices', 'company_id');
        $this->addIndexIfNotExists('invoices', 'created_at');
        $this->addIndexIfNotExists('vehicles', 'company_id');
        $this->addIndexIfNotExists('vehicles', 'company_branch_id');
        $this->addIndexIfNotExists('fuel_refills', 'company_id');
        $this->addIndexIfNotExists('fuel_refills', 'vehicle_id');
        $this->addIndexIfNotExists('maintenance_requests', 'company_id');
        $this->addIndexIfNotExists('maintenance_requests', 'vehicle_id');
        $this->addIndexIfNotExists('payments', 'order_id');
    }

    public function down(): void
    {
        $this->dropIndexIfExists('orders', 'company_id');
        $this->dropIndexIfExists('orders', 'vehicle_id');
        $this->dropIndexIfExists('orders', 'created_at');
        $this->dropIndexIfExists('invoices', 'company_id');
        $this->dropIndexIfExists('invoices', 'created_at');
        $this->dropIndexIfExists('vehicles', 'company_id');
        $this->dropIndexIfExists('vehicles', 'company_branch_id');
        $this->dropIndexIfExists('fuel_refills', 'company_id');
        $this->dropIndexIfExists('fuel_refills', 'vehicle_id');
        $this->dropIndexIfExists('maintenance_requests', 'company_id');
        $this->dropIndexIfExists('maintenance_requests', 'vehicle_id');
        $this->dropIndexIfExists('payments', 'order_id');
    }

    private function addIndexIfNotExists(string $table, string $column): void
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column)) {
            return;
        }
        $indexName = "{$table}_{$column}_index";
        try {
            Schema::table($table, fn (Blueprint $t) => $t->index($column, $indexName));
        } catch (\Throwable $e) {
            // Index may already exist
        }
    }

    private function dropIndexIfExists(string $table, string $column): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }
        $indexName = "{$table}_{$column}_index";
        try {
            Schema::table($table, fn (Blueprint $t) => $t->dropIndex($indexName));
        } catch (\Throwable $e) {
            // Index may not exist
        }
    }
};
