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
        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            $table->uuid('student_id');
            $table->foreign('student_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->uuid('quiz_id');
            $table->foreign('quiz_id')->references('id')->on('quizzes')->onDelete('cascade');
            
            $table->decimal('score', 5, 2)->nullable(); // Percentage score
            $table->boolean('is_passed')->default(false);
            $table->integer('attempt_number'); // Which attempt this is
            $table->integer('time_spent')->nullable(); // Time spent in seconds
            
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('submitted_at')->nullable();
            
            $table->timestamps();
            
            $table->index(['student_id']);
            $table->index(['quiz_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_attempts');
    }
};
