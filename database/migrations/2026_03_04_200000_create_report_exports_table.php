<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_exports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('notifiable_type');
            $table->unsignedBigInteger('notifiable_id');
            $table->string('type'); // mileage_pdf, mileage_excel, vehicle_report_pdf, vehicle_report_excel, invoice_pdf
            $table->string('file_path');
            $table->string('filename');
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->index(['notifiable_type', 'notifiable_id']);
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_exports');
    }
};
