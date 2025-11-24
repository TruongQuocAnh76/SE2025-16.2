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
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Payment details
            $table->enum('payment_type', ['COURSE', 'MEMBERSHIP'])->default('COURSE');
            $table->uuid('course_id')->nullable(); // if payment_type = COURSE
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('set null');
            $table->enum('membership_plan', ['PREMIUM'])->nullable(); // if payment_type = MEMBERSHIP (only Premium is paid)
            
            // Payment gateway
            $table->enum('payment_method', ['PAYPAL', 'VNPAY', 'STRIPE'])->default('PAYPAL');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('USD');
            
            // Transaction info
            $table->string('transaction_id')->unique()->nullable();
            $table->enum('status', ['PENDING', 'PROCESSING', 'COMPLETED', 'FAILED', 'REFUNDED'])->default('PENDING');
            $table->text('payment_details')->nullable(); // JSON data from payment gateway
            
            // Metadata
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('transaction_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
