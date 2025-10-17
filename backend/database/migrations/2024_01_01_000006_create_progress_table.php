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
        Schema::create('progress', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            $table->uuid('student_id');
            $table->foreign('student_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->uuid('lesson_id');
            $table->foreign('lesson_id')->references('id')->on('lessons')->onDelete('cascade');
            
            $table->boolean('is_completed')->default(false);
            $table->integer('time_spent')->default(0); // Time spent in seconds
            $table->timestamp('last_accessed_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();
            
            $table->timestamps();
            
            $table->unique(['student_id', 'lesson_id']);
            $table->index(['student_id']);
            $table->index(['lesson_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('progress');
    }
};
