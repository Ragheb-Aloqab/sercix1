<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();

            // من قام بالنشاط
            $table->string('actor_type'); // admin | technician | company | customer | system
            $table->unsignedBigInteger('actor_id')->nullable();

            // نوع النشاط
            $table->string('action'); // order_created, order_assigned, payment_paid ...

            // على ماذا تم النشاط
            $table->string('subject_type'); // order, payment, user
            $table->unsignedBigInteger('subject_id');

            // وصف للعرض
            $table->text('description');

            // القيم قبل وبعد
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();

            // معلومات إضافية
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamps();

            // تحسينات الأداء
            $table->index(['actor_type', 'actor_id']);
            $table->index(['subject_type', 'subject_id']);
            $table->index('action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};