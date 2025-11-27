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
        Schema::create('questions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->text('question_text');
            $table->enum('question_type', ['MULTIPLE_CHOICE', 'CHECKBOX', 'TRUE_FALSE', 'SHORT_ANSWER', 'ESSAY']);
            $table->integer('points')->default(1);
            $table->integer('order_index');
            
            // Multiple choice options stored as JSON array
            $table->json('options')->nullable(); // ["Option A", "Option B", "Option C", "Option D"]
            $table->text('correct_answer'); // For auto-grading
            $table->text('explanation')->nullable(); // Explanation shown after answering
            
            $table->uuid('quiz_id');
            $table->foreign('quiz_id')->references('id')->on('quizzes')->onDelete('cascade');
            
            $table->timestamps();
            
            $table->index(['quiz_id']);
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
