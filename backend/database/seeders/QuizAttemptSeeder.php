<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QuizAttemptSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get students
        $alice = DB::table('users')->where('email', 'alice.student@example.com')->first();
        $bob = DB::table('users')->where('email', 'bob.learner@example.com')->first();
        $charlie = DB::table('users')->where('email', 'charlie.dev@example.com')->first();

        // Get quizzes
        $vueFundamentalsQuiz = DB::table('quizzes')->where('title', 'Vue.js Fundamentals Quiz')->first();
        $webKnowledgeCheck = DB::table('quizzes')->where('title', 'Web Development Knowledge Check')->first();

        DB::table('quiz_attempts')->insert([
            [
                'id' => Str::uuid(),
                'student_id' => $alice->id,
                'quiz_id' => $vueFundamentalsQuiz->id,
                'score' => 85.00,
                'is_passed' => true,
                'attempt_number' => 1,
                'time_spent' => 1450, // 24 minutes
                'started_at' => now()->subDays(5),
                'submitted_at' => now()->subDays(5)->addMinutes(24),
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(5)->addMinutes(24),
                'grading_status' => 'graded',
            ],
            [
                'id' => Str::uuid(),
                'student_id' => $bob->id,
                'quiz_id' => $webKnowledgeCheck->id,
                'score' => 65.00,
                'is_passed' => true,
                'attempt_number' => 1,
                'time_spent' => 980, // 16 minutes
                'started_at' => now()->subDays(3),
                'submitted_at' => now()->subDays(3)->addMinutes(16),
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subDays(3)->addMinutes(16),
                'grading_status' => 'graded',
            ],
            [
                'id' => Str::uuid(),
                'student_id' => $bob->id,
                'quiz_id' => $webKnowledgeCheck->id,
                'score' => 75.00,
                'is_passed' => true,
                'attempt_number' => 2,
                'time_spent' => 720, // 12 minutes
                'started_at' => now()->subDays(1),
                'submitted_at' => now()->subDays(1)->addMinutes(12),
                'created_at' => now()->subDays(1),
                'updated_at' => now()->subDays(1)->addMinutes(12),
                'grading_status' => 'graded',
            ],
            [
                'id' => Str::uuid(),
                'student_id' => $charlie->id,
                'quiz_id' => $vueFundamentalsQuiz->id,
                'score' => 45.00,
                'is_passed' => false,
                'attempt_number' => 1,
                'time_spent' => 1200, // 20 minutes
                'started_at' => now()->subDays(2),
                'submitted_at' => now()->subDays(2)->addMinutes(20),
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2)->addMinutes(20),
                'grading_status' => 'graded',
            ],
            [
                'id' => Str::uuid(),
                'student_id' => $charlie->id,
                'quiz_id' => $vueFundamentalsQuiz->id,
                'score' => 78.00,
                'is_passed' => true,
                'attempt_number' => 2,
                'time_spent' => 1650, // 27.5 minutes
                'started_at' => now()->subHours(6),
                'submitted_at' => now()->subHours(6)->addMinutes(28),
                'created_at' => now()->subHours(6),
                'updated_at' => now()->subHours(6)->addMinutes(28),
                'grading_status' => 'graded',
            ],
            // New Attempt: Pending Manual Grading (To test UI hiding logic)
            [
                'id' => Str::uuid(),
                'student_id' => $alice->id,
                'quiz_id' => $webKnowledgeCheck->id,
                'score' => null,
                'is_passed' => false,
                'attempt_number' => 2,
                'time_spent' => 600,
                'started_at' => now()->subMinutes(30),
                'submitted_at' => now()->subMinutes(10),
                'created_at' => now()->subMinutes(30),
                'updated_at' => now()->subMinutes(10),
                'grading_status' => 'pending_manual',
            ],
        ]);
    }
}
