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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();

            // Company auth: phone + password + OTP verification
            $table->string('company_name');
            $table->string('email')->nullable()->unique();

            $table->string('phone')->unique();
            $table->string('password');

            $table->timestamp('phone_verified_at')->nullable();

            // optional company info
            $table->string('contact_person')->nullable();
            $table->string('city')->nullable();
            $table->text('address')->nullable();

            $table->enum('status', ['active', 'suspended'])->default('active')->index();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
