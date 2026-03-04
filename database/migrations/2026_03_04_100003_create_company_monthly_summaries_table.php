<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_monthly_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->unsignedTinyInteger('month');
            $table->unsignedSmallInteger('year');
            $table->decimal('total_monthly_mileage_km', 14, 2)->default(0)->comment('Sum of all vehicles mileage for month');
            $table->decimal('estimated_market_cost_sar', 14, 2)->default(0)->comment('total_monthly_mileage * 0.37');
            $table->decimal('average_market_operating_cost_sar', 10, 2)->nullable()->comment('Per km rate used');
            $table->timestamps();

            $table->unique(['company_id', 'month', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_monthly_summaries');
    }
};
