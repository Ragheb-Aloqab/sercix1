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
        Schema::create('otp_verifications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();

            $table->enum('purpose', ['register', 'login', 'reset_password'])->index();

            // store hashed otp (recommended)
            $table->string('otp_hash');

            $table->timestamp('expires_at')->index();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->unsignedTinyInteger('max_attempts')->default(3);

            $table->timestamp('verified_at')->nullable();
            $table->timestamp('last_sent_at')->nullable();

            $table->timestamps();

            $table->index(['company_id', 'purpose']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otp_verifications');
    }
};
