<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('registration_document_path')->nullable()->after('image_path');
            $table->date('registration_expiry_date')->nullable()->after('registration_document_path')->index();
            $table->string('insurance_document_path')->nullable()->after('registration_expiry_date');
            $table->date('insurance_expiry_date')->nullable()->after('insurance_document_path')->index();
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn([
                'registration_document_path',
                'registration_expiry_date',
                'insurance_document_path',
                'insurance_expiry_date',
            ]);
        });
    }
};
