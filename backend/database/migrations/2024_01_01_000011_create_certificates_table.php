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
        Schema::create('certificates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('certificate_number')->unique(); // Human-readable cert number
            
            $table->uuid('student_id');
            $table->foreign('student_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->uuid('course_id');
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            
            $table->enum('status', ['PENDING', 'ISSUED', 'FAILED', 'REVOKED'])->default('PENDING');
            $table->decimal('final_score', 5, 2); // Student's final score
            
            // Off-chain storage
            $table->string('pdf_url')->nullable(); // S3 URL to PDF certificate
            $table->string('pdf_hash')->nullable(); // Hash of PDF for verification
            
            $table->timestamp('issued_at')->useCurrent();
            $table->timestamp('expires_at')->nullable(); // For certificates with expiry
            $table->timestamp('revoked_at')->nullable();
            $table->string('revocation_reason')->nullable();
            
            $table->timestamps();
            
            $table->index(['student_id']);
            $table->index(['course_id']);
            $table->index(['certificate_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
