<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add 'quotation_invoice' to attachments type enum.
     * Required for driver service request workflow: quotation must be uploaded before company approval.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE attachments MODIFY COLUMN type ENUM(
                'before_photo', 'after_photo', 'signature', 'other', 'driver_invoice', 'quotation_invoice'
            )");
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::table('attachments')->where('type', 'quotation_invoice')->delete();
            DB::statement("ALTER TABLE attachments MODIFY COLUMN type ENUM(
                'before_photo', 'after_photo', 'signature', 'other', 'driver_invoice'
            )");
        } else {
            DB::table('attachments')->where('type', 'quotation_invoice')->delete();
        }
    }
};
