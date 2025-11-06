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
        Schema::table('courses', function (Blueprint $table) {
            //
            $table->text('long_description')->nullable();
            $table->string('curriculum')->nullable();
            $table->string('category')->nullable();
            $table->string('language')->nullable();
            $table->decimal('discount', 8, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            //
            $table->dropColumn([
                'long_description',
                'curriculum',
                'category',
                'language',
                'discount'
            ]);
        });
    }
};
