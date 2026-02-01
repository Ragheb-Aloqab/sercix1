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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();

            // assigned technician (user with role=technician)
            $table->foreignId('technician_id')->nullable()->constrained('users')->nullOnDelete();

            $table->enum('status', [
                'pending', 'accepted', 'on_the_way', 'in_progress', 'completed', 'cancelled','on_hold','paid'
            ])->default('pending')->index();

            $table->timestamp('scheduled_at')->nullable()->index();

            // location snapshot
            $table->string('city')->nullable();
            $table->text('address')->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index(['technician_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
