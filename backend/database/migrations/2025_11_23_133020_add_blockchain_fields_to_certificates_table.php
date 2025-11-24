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
        Schema::table('certificates', function (Blueprint $table) {
            // Blockchain integration fields
            $table->enum('blockchain_status', ['pending', 'issued', 'failed'])
                  ->default('pending')
                  ->after('status');
            
            $table->string('blockchain_transaction_hash', 66)
                  ->nullable()
                  ->after('blockchain_status');
            
            $table->bigInteger('blockchain_block_number')
                  ->nullable()
                  ->after('blockchain_transaction_hash');
            
            $table->integer('blockchain_confirmations')
                  ->default(0)
                  ->after('blockchain_block_number');
            
            $table->string('blockchain_gas_used')
                  ->nullable()
                  ->after('blockchain_confirmations');
            
            $table->text('blockchain_error')
                  ->nullable()
                  ->after('blockchain_gas_used');
            
            $table->timestamp('blockchain_issued_at')
                  ->nullable()
                  ->after('blockchain_error');
            
            // Student's blockchain wallet address for certificate ownership
            $table->string('student_wallet_address', 42)
                  ->nullable()
                  ->after('blockchain_issued_at');
            
            // Add indexes for blockchain fields
            $table->index('blockchain_status');
            $table->index('blockchain_transaction_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->dropIndex(['blockchain_status']);
            $table->dropIndex(['blockchain_transaction_hash']);
            
            $table->dropColumn([
                'blockchain_status',
                'blockchain_transaction_hash', 
                'blockchain_block_number',
                'blockchain_confirmations',
                'blockchain_gas_used',
                'blockchain_error',
                'blockchain_issued_at',
                'student_wallet_address'
            ]);
        });
    }
};
