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
        Schema::table('teacher_applications', function (Blueprint $table) {
            $table->string('certificate_file_path')->nullable()->after('expiry_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teacher_applications', function (Blueprint $table) {
            $table->dropColumn('certificate_file_path');
        });
    }
};
