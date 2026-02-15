<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            // Step 1: Expand ENUM to include new values (so we can migrate data)
            DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM(
                'pending','requested','accepted','assigned','on_the_way','in_progress','completed','cancelled','on_hold','paid',
                'pending_company','approved_by_company','pending_assignment','assigned_to_technician'
            ) DEFAULT 'pending'");

            // Step 2: Map old statuses to new ones
            $mapping = [
                'requested' => 'pending_company',
                'pending' => 'approved_by_company',
                'accepted' => 'pending_assignment',
                'assigned' => 'assigned_to_technician',
                'on_the_way' => 'in_progress',
                'on_hold' => 'pending_assignment',
                'paid' => 'completed',
            ];

            foreach ($mapping as $old => $new) {
                DB::table('orders')->where('status', $old)->update(['status' => $new]);
            }

            // Step 3: Final ENUM with only new values
            DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM(
                'pending_company',
                'approved_by_company',
                'pending_assignment',
                'assigned_to_technician',
                'in_progress',
                'completed',
                'cancelled'
            ) DEFAULT 'pending_company'");
        } else {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('status', 50)->default('pending_company')->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            $mapping = [
                'pending_company' => 'pending',
                'approved_by_company' => 'pending',
                'pending_assignment' => 'pending',
                'assigned_to_technician' => 'assigned',
                'in_progress' => 'in_progress',
                'completed' => 'completed',
                'cancelled' => 'cancelled',
            ];

            foreach ($mapping as $new => $old) {
                DB::table('orders')->where('status', $new)->update(['status' => $old]);
            }

            DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM(
                'pending','requested','accepted','assigned','on_the_way','in_progress','completed','cancelled','on_hold','paid'
            ) DEFAULT 'pending'");
        } else {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('status', 50)->default('pending')->change();
            });
        }
    }
};
