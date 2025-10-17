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
        Schema::create('courses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('thumbnail')->nullable(); // URL to course image
            $table->enum('status', ['DRAFT', 'PENDING', 'PUBLISHED', 'ARCHIVED'])->default('DRAFT');
            $table->enum('level', ['BEGINNER', 'INTERMEDIATE', 'ADVANCED', 'EXPERT'])->default('BEGINNER');
            $table->integer('duration')->nullable(); // Estimated duration in minutes
            $table->decimal('price', 10, 2)->nullable();
            
            // Completion requirements
            $table->integer('passing_score')->default(70); // Percentage needed to pass
            $table->integer('required_completion')->default(100); // Percentage of content to complete
            
            $table->uuid('teacher_id');
            $table->foreign('teacher_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->timestamps();
            $table->timestamp('published_at')->nullable();
            
            $table->index(['teacher_id']);
            $table->index(['status']);
            $table->index(['slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
