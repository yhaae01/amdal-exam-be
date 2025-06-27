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
        Schema::create('exam_batch_user', function (Blueprint $table) {
            $table->uuid('exam_batch_id');
            $table->uuid('user_id');
            $table->timestamps();

            $table->primary(['exam_batch_id', 'user_id']);
            $table->foreign('exam_batch_id')->references('id')->on('exam_batches')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_batch_user');
    }
};
