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
        Schema::create('lessons', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('content_type', ['VIDEO', 'PDF', 'DOCUMENT', 'LINK', 'TEXT']);
            $table->string('content_url')->nullable(); // S3 URL for video/PDF
            $table->text('text_content')->nullable(); // For text-based lessons
            $table->integer('duration')->nullable(); // Duration in seconds for videos
            $table->integer('order_index'); // Order within the module
            $table->boolean('is_free')->default(false); // Preview lessons
            
            $table->uuid('module_id');
            $table->foreign('module_id')->references('id')->on('modules')->onDelete('cascade');
            
            $table->timestamps();
            
            $table->index(['module_id', 'order_index']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
