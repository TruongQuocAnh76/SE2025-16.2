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
        Schema::create('quizzes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('quiz_type', ['PRACTICE', 'GRADED', 'FINAL'])->default('PRACTICE');
            $table->integer('time_limit')->nullable(); // Time limit in minutes
            $table->integer('passing_score')->default(70); // Percentage needed to pass
            $table->integer('max_attempts')->nullable(); // Null = unlimited
            $table->integer('order_index'); // Order within course
            $table->boolean('is_active')->default(true);
            
            $table->uuid('course_id');
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            
            $table->timestamps();
            
            $table->index(['course_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};
