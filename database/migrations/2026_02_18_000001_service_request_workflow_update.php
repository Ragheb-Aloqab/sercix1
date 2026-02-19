<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Service Request Workflow Update per specification.
     * New flow: pending_approval → approved/rejected → in_progress → pending_confirmation → completed
     */
    public function up(): void
    {
        // 1. Add rejection_reason to orders
        Schema::table('orders', function (Blueprint $table) {
            $table->text('rejection_reason')->nullable()->after('notes');
        });

        // 2. Add custom service support to order_services
        Schema::table('order_services', function (Blueprint $table) {
            $table->string('custom_service_name')->nullable()->after('service_id');
            $table->text('custom_service_description')->nullable()->after('custom_service_name');
        });

        // 3. Make service_id nullable for custom services (drop FK, change, re-add)
        Schema::table('order_services', function (Blueprint $table) {
            $table->dropForeign(['service_id']);
        });
        Schema::table('order_services', function (Blueprint $table) {
            $table->unsignedBigInteger('service_id')->nullable()->change();
        });
        Schema::table('order_services', function (Blueprint $table) {
            $table->foreign('service_id')->references('id')->on('services')->nullOnDelete();
        });

        // 4. Add invoice attachment type
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE attachments MODIFY COLUMN type ENUM(
                'before_photo', 'after_photo', 'signature', 'other', 'driver_invoice'
            )");
        }

        // 5. Update order status enum and migrate data
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM(
                'pending_approval', 'approved', 'in_progress', 'pending_confirmation',
                'completed', 'rejected', 'cancelled',
                'pending_company', 'approved_by_company', 'pending_assignment', 'assigned_to_technician'
            )");

            $mapping = [
                'pending_company' => 'pending_approval',
                'approved_by_company' => 'approved',
                'pending_assignment' => 'approved',
                'assigned_to_technician' => 'in_progress',
            ];

            foreach ($mapping as $old => $new) {
                DB::table('orders')->where('status', $old)->update(['status' => $new]);
            }

            DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM(
                'pending_approval', 'approved', 'in_progress', 'pending_confirmation',
                'completed', 'rejected', 'cancelled'
            ) DEFAULT 'pending_approval'");
        } else {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('status', 50)->default('pending_approval')->change();
            });
        }
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('rejection_reason');
        });

        Schema::table('order_services', function (Blueprint $table) {
            $table->dropForeign(['service_id']);
        });
        Schema::table('order_services', function (Blueprint $table) {
            $table->dropColumn(['custom_service_name', 'custom_service_description']);
            $table->unsignedBigInteger('service_id')->nullable(false)->change();
        });
        Schema::table('order_services', function (Blueprint $table) {
            $table->foreign('service_id')->references('id')->on('services')->cascadeOnDelete();
        });

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE attachments MODIFY COLUMN type ENUM(
                'before_photo', 'after_photo', 'signature', 'other'
            )");

            DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM(
                'pending_company', 'approved_by_company', 'pending_assignment',
                'assigned_to_technician', 'in_progress', 'completed', 'cancelled'
            ) DEFAULT 'pending_company'");
        }
    }
};
