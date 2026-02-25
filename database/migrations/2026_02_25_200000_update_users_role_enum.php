<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Replace technician with super_admin. Technicians are removed from the system.
     */
    public function up(): void
    {
        // Convert any existing 'technician' to 'admin' before altering enum
        DB::table('users')->where('role', 'technician')->update(['role' => 'admin']);
        // Add super_admin to enum (MySQL: must include all values when modifying)
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'super_admin') NOT NULL DEFAULT 'admin'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'technician') NOT NULL DEFAULT 'admin'");
    }
};
