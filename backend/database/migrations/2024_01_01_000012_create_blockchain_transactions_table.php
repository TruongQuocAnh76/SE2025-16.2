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
        Schema::create('blockchain_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('transaction_hash')->unique(); // On-chain transaction hash
            $table->enum('network', ['ETHEREUM', 'POLYGON', 'HYPERLEDGER']);
            $table->enum('status', ['PENDING', 'CONFIRMED', 'FAILED'])->default('PENDING');
            
            // On-chain data
            $table->string('certificate_hash'); // Hash stored on blockchain
            $table->json('metadata')->nullable(); // Additional metadata stored on-chain
            $table->bigInteger('block_number')->nullable();
            $table->string('gas_used')->nullable();
            
            $table->uuid('certificate_id');
            $table->foreign('certificate_id')->references('id')->on('certificates')->onDelete('cascade');
            
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
            
            $table->unique(['certificate_id']);
            $table->index(['transaction_hash']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blockchain_transactions');
    }
};
