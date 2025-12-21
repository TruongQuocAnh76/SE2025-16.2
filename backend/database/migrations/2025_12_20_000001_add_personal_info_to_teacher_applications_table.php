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
            // Personal Information
            $table->string('full_name')->after('user_id');
            $table->string('email')->after('full_name');
            $table->text('bio')->nullable()->after('email');
            $table->enum('gender', ['MALE', 'FEMALE', 'OTHER'])->nullable()->after('bio');
            $table->string('phone')->nullable()->after('gender');
            $table->date('date_of_birth')->nullable()->after('phone');
            $table->string('country')->nullable()->after('date_of_birth');
            $table->string('avatar_url')->nullable()->after('country');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teacher_applications', function (Blueprint $table) {
            $table->dropColumn([
                'full_name',
                'email',
                'bio',
                'gender',
                'phone',
                'date_of_birth',
                'country',
                'avatar_url'
            ]);
        });
    }
};
