<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BlockchainTransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get certificates
        $issuedCertificate = DB::table('certificates')->where('status', 'ISSUED')->first();
        $pendingCertificate = DB::table('certificates')->where('status', 'PENDING')->first();

        $transactions = [];

        if ($issuedCertificate) {
            $transactions[] = [
                'id' => Str::uuid(),
                'transaction_hash' => '0x1234567890abcdef1234567890abcdef12345678901234567890abcdef123456',
                'network' => 'POLYGON',
                'status' => 'CONFIRMED',
                'certificate_hash' => hash('sha256', $issuedCertificate->pdf_hash),
                'metadata' => json_encode([
                    'certificate_number' => $issuedCertificate->certificate_number,
                    'student_name' => 'Alice Cooper',
                    'course_title' => 'Web Development Fundamentals',
                    'issue_date' => $issuedCertificate->issued_at,
                    'issuer' => 'Certchain Platform'
                ]),
                'block_number' => 45782341,
                'gas_used' => '0x5208',
                'certificate_id' => $issuedCertificate->id,
                'confirmed_at' => now()->subDays(5)->addMinutes(15),
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(5)->addMinutes(15),
            ];
        }

        if ($pendingCertificate) {
            $transactions[] = [
                'id' => Str::uuid(),
                'transaction_hash' => '0xabcdef1234567890abcdef1234567890abcdef1234567890abcdef1234567890',
                'network' => 'POLYGON',
                'status' => 'PENDING',
                'certificate_hash' => hash('sha256', 'charlie-vue-certificate-pending'),
                'metadata' => json_encode([
                    'certificate_number' => $pendingCertificate->certificate_number,
                    'student_name' => 'Charlie Brown',
                    'course_title' => 'Complete Vue.js 3 Development Course',
                    'issue_date' => $pendingCertificate->issued_at,
                    'issuer' => 'Certchain Platform'
                ]),
                'block_number' => null,
                'gas_used' => null,
                'certificate_id' => $pendingCertificate->id,
                'confirmed_at' => null,
                'created_at' => now()->subMinutes(30),
                'updated_at' => now()->subMinutes(30),
            ];
        }

        if (!empty($transactions)) {
            DB::table('blockchain_transactions')->insert($transactions);
        }
    }
}
