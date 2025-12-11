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
        Schema::create('course_tag', function (Blueprint $table) {
            // Không cần 'id' hay timestamps cho bảng pivot đơn giản
            $table->uuid('course_id');
            $table->uuid('tag_id');

            // Định nghĩa khóa ngoại
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            $table->foreign('tag_id')->references('id')->on('tags')->onDelete('cascade');

            // Đặt cả hai làm khóa chính
            $table->primary(['course_id', 'tag_id']);
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_tag');
    }
};
