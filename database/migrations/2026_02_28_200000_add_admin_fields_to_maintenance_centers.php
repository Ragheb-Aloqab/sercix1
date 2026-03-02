<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maintenance_centers', function (Blueprint $table) {
            $table->json('service_categories')->nullable()->after('city');
            $table->string('status', 20)->default('active')->after('is_active')->index(); // active, suspended
            $table->decimal('total_earnings', 14, 2)->default(0)->after('status');
            $table->decimal('paid_amount', 14, 2)->default(0)->after('total_earnings');
            $table->decimal('pending_payments', 14, 2)->default(0)->after('paid_amount');
            $table->unsignedInteger('total_completed_jobs')->default(0)->after('pending_payments');
            $table->decimal('rating', 3, 2)->nullable()->after('total_completed_jobs'); // future-ready
        });

        // Sync existing is_active to status
        DB::table('maintenance_centers')->where('is_active', false)->update(['status' => 'suspended']);
    }

    public function down(): void
    {
        Schema::table('maintenance_centers', function (Blueprint $table) {
            $table->dropColumn([
                'status', 'service_categories', 'total_earnings', 'paid_amount',
                'pending_payments', 'total_completed_jobs', 'rating',
            ]);
        });
    }
};
