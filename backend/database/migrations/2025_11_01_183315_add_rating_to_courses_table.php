<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            // Thêm 2 cột mới
            $table->decimal('average_rating', 2, 1)->nullable()->default(0.0);
            $table->unsignedInteger('review_count')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            // Logic để xóa khi rollback
            $table->dropColumn('average_rating');
            $table->dropColumn('review_count');
        });
    }
};