<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // This migration creates questions for an exam.
    // Each option is linked to a question and can be marked as correct or incorrect.
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            $table->uuid('exam_id');
            $table->foreign('exam_id')->references('id')->on('exams')->onDelete('cascade');

            $table->text('question_text');
            $table->integer('order')->default(0);
            $table->enum('question_type', ['multiple_choice', 'essay'])->default('multiple_choice');
            $table->float('weight')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
