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
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('email')->unique();
            $table->string('password')->nullable(); // Nullable for OAuth users
            $table->string('first_name');
            $table->string('last_name');
            $table->string('avatar', 500)->nullable(); // URL to profile picture (increased to 500 for OAuth providers)
            $table->text('bio')->nullable();
            $table->enum('role', ['STUDENT', 'TEACHER', 'ADMIN'])->default('STUDENT');
            $table->enum('auth_provider', ['EMAIL', 'GOOGLE', 'FACEBOOK', 'GITHUB'])->default('EMAIL');
            $table->boolean('is_email_verified')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['role']);
            $table->index(['email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
