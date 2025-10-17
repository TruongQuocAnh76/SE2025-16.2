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
        Schema::create('answers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->text('answer_text');
            $table->boolean('is_correct')->nullable(); // Null until graded
            $table->integer('points_awarded')->default(0);
            
            $table->uuid('attempt_id');
            $table->foreign('attempt_id')->references('id')->on('quiz_attempts')->onDelete('cascade');
            
            $table->uuid('question_id');
            $table->foreign('question_id')->references('id')->on('questions')->onDelete('cascade');
            
            $table->timestamps();
            
            $table->index(['attempt_id']);
            $table->index(['question_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('answers');
    }
};
