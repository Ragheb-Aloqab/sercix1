<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('announcement_reads');
        Schema::create('announcement_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('announcement_id')->constrained()->cascadeOnDelete();
            $table->string('notifiable_type'); // Company, User
            $table->unsignedBigInteger('notifiable_id');
            $table->timestamp('read_at');
            $table->timestamps();

            $table->unique(['announcement_id', 'notifiable_type', 'notifiable_id'], 'ann_reads_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcement_reads');
    }
};
