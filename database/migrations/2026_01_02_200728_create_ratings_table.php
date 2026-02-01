<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            $table->foreignId('technician_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->unsignedTinyInteger('speed')->nullable();
            $table->unsignedTinyInteger('quality')->nullable();
            $table->unsignedTinyInteger('behavior')->nullable(); 

            $table->unsignedTinyInteger('overall')->nullable();

            $table->text('comment')->nullable();

            $table->timestamps();

            $table->unique(['company_id', 'order_id']);

            $table->index(['technician_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
