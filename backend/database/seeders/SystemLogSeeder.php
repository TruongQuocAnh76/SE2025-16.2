<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SystemLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some user IDs
        $admin = DB::table('users')->where('role', 'ADMIN')->first();
        $alice = DB::table('users')->where('email', 'alice.student@example.com')->first();
        $john = DB::table('users')->where('email', 'john.teacher@certchain.com')->first();

        DB::table('system_logs')->insert([
            [
                'id' => Str::uuid(),
                'level' => 'INFO',
                'message' => 'User successfully logged in',
                'context' => json_encode([
                    'user_email' => 'alice.student@example.com',
                    'login_method' => 'email',
                    'session_id' => 'sess_abc123'
                ]),
                'user_id' => $alice->id,
                'ip_address' => '192.168.1.100',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'created_at' => now()->subHours(2),
                'updated_at' => now()->subHours(2),
            ],
            [
                'id' => Str::uuid(),
                'level' => 'INFO',
                'message' => 'Course enrollment completed',
                'context' => json_encode([
                    'student_id' => $alice->id,
                    'course_slug' => 'complete-vuejs-3-development',
                    'enrollment_type' => 'paid'
                ]),
                'user_id' => $alice->id,
                'ip_address' => '192.168.1.100',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'created_at' => now()->subDays(15),
                'updated_at' => now()->subDays(15),
            ],
            [
                'id' => Str::uuid(),
                'level' => 'WARNING',
                'message' => 'Failed login attempt',
                'context' => json_encode([
                    'attempted_email' => 'unknown@example.com',
                    'failure_reason' => 'invalid_credentials',
                    'attempt_count' => 3
                ]),
                'user_id' => null,
                'ip_address' => '203.0.113.42',
                'user_agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36',
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ],
            [
                'id' => Str::uuid(),
                'level' => 'INFO',
                'message' => 'Certificate issued successfully',
                'context' => json_encode([
                    'certificate_number' => 'CERT-WEB-2024-001',
                    'student_id' => $alice->id,
                    'course_slug' => 'web-development-fundamentals',
                    'blockchain_network' => 'polygon'
                ]),
                'user_id' => $admin->id,
                'ip_address' => '192.168.1.50',
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(5),
            ],
            [
                'id' => Str::uuid(),
                'level' => 'ERROR',
                'message' => 'Blockchain transaction failed',
                'context' => json_encode([
                    'transaction_hash' => '0xfailed123456789',
                    'error_code' => 'insufficient_gas',
                    'certificate_id' => 'cert_pending_123',
                    'retry_count' => 2
                ]),
                'user_id' => null,
                'ip_address' => null,
                'user_agent' => null,
                'created_at' => now()->subHours(6),
                'updated_at' => now()->subHours(6),
            ],
            [
                'id' => Str::uuid(),
                'level' => 'INFO',
                'message' => 'Course published successfully',
                'context' => json_encode([
                    'course_slug' => 'complete-vuejs-3-development',
                    'teacher_id' => $john->id,
                    'review_status' => 'approved'
                ]),
                'user_id' => $john->id,
                'ip_address' => '192.168.1.75',
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
                'created_at' => now()->subDays(20),
                'updated_at' => now()->subDays(20),
            ],
            [
                'id' => Str::uuid(),
                'level' => 'CRITICAL',
                'message' => 'Database connection timeout',
                'context' => json_encode([
                    'database_host' => 'db-primary',
                    'timeout_duration' => '30s',
                    'affected_operations' => ['user_login', 'course_access']
                ]),
                'user_id' => null,
                'ip_address' => null,
                'user_agent' => null,
                'created_at' => now()->subDays(7),
                'updated_at' => now()->subDays(7),
            ],
            [
                'id' => Str::uuid(),
                'level' => 'INFO',
                'message' => 'Quiz attempt submitted',
                'context' => json_encode([
                    'quiz_title' => 'Vue.js Fundamentals Quiz',
                    'student_id' => $alice->id,
                    'score' => 85.0,
                    'time_spent' => 1450,
                    'attempt_number' => 1
                ]),
                'user_id' => $alice->id,
                'ip_address' => '192.168.1.100',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(5),
            ],
        ]);
    }
}
