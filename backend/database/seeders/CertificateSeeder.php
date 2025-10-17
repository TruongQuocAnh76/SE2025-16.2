<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CertificateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get students
        $alice = DB::table('users')->where('email', 'alice.student@example.com')->first();
        $charlie = DB::table('users')->where('email', 'charlie.dev@example.com')->first();

        // Get courses
        $webFundamentals = DB::table('courses')->where('slug', 'web-development-fundamentals')->first();
        $vueCourse = DB::table('courses')->where('slug', 'complete-vuejs-3-development')->first();

        DB::table('certificates')->insert([
            [
                'id' => Str::uuid(),
                'certificate_number' => 'CERT-WEB-2024-001',
                'student_id' => $alice->id,
                'course_id' => $webFundamentals->id,
                'status' => 'ISSUED',
                'final_score' => 88.50,
                'pdf_url' => 'https://certchain-certificates.s3.amazonaws.com/certificates/alice-web-fundamentals.pdf',
                'pdf_hash' => hash('sha256', 'alice-web-fundamentals-certificate-content'),
                'issued_at' => now()->subDays(5),
                'expires_at' => null,
                'revoked_at' => null,
                'revocation_reason' => null,
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(5),
            ],
            [
                'id' => Str::uuid(),
                'certificate_number' => 'CERT-VUE-2024-001',
                'student_id' => $charlie->id,
                'course_id' => $vueCourse->id,
                'status' => 'PENDING',
                'final_score' => 82.75,
                'pdf_url' => null,
                'pdf_hash' => null,
                'issued_at' => now(),
                'expires_at' => now()->addYears(2),
                'revoked_at' => null,
                'revocation_reason' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
