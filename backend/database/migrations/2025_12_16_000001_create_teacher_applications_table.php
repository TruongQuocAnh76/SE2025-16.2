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
        Schema::create('teacher_applications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->enum('status', ['PENDING', 'APPROVED', 'REJECTED'])->default('PENDING');
            
            // Certificate/Qualification Details
            $table->string('certificate_title'); // Course name or certificate title
            $table->string('issuer'); // School / Organization
            $table->date('issue_date');
            $table->date('expiry_date')->nullable(); // Optional expiry date
            
            // Review Information
            $table->uuid('reviewed_by')->nullable();
            $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            
            $table->timestamps();
            
            $table->index(['user_id']);
            $table->index(['status']);
            $table->index(['reviewed_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_applications');
    }
};
