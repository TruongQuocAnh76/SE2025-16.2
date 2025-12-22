<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QuizTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Get a specific teacher and course
        $teacher = DB::table('users')->where('email', 'john.teacher@certchain.com')->first();
        if (!$teacher) {
            $this->command->error('Teacher john.teacher@certchain.com not found. Please run DatabaseSeeder first.');
            return;
        }

        // Use specific Course ID if possible, otherwise find by title
        $courseId = '62add7a8-98be-4157-92f8-6b83d63deb29'; 
        $course = DB::table('courses')->where('id', $courseId)->first();
        
        if (!$course) {
             $course = DB::table('courses')->where('title', 'Laravel API Development Masterclass')->first();
        }

        if (!$course) {
             $this->command->error('No courses found.');
             return;
        }

        // 2. Create a specific Test Quiz
        $quizId = Str::uuid();
        DB::table('quizzes')->insert([
            'id' => $quizId,
            'title' => 'Test Quiz: Checkbox & Grading',
            'description' => 'A quiz specifically designed to test strict checkbox grading and manual short answer grading.',
            'quiz_type' => 'GRADED',
            'time_limit' => 30,
            'passing_score' => 50,
            'max_attempts' => 10,
            'order_index' => 99,
            'is_active' => true,
            'course_id' => $course->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info("Created Test Quiz: $quizId (Course: {$course->title})");

        // 3. Add Questions
        
        // Q1: Checkbox (Multiple matches required)
        DB::table('questions')->insert([
            'id' => Str::uuid(),
            'quiz_id' => $quizId,
            'question_text' => 'Which of the following are PHP frameworks? (Select 2)',
            'question_type' => 'CHECKBOX',
            'points' => 5,
            'order_index' => 1,
            // JSON encoded options
            'options' => json_encode(['Laravel', 'React', 'Symfony', 'Vue']),
            // Correct answer is a JSON array of strings
            'correct_answer' => json_encode(['Laravel', 'Symfony']),
            'explanation' => 'Laravel and Symfony are PHP frameworks. React and Vue are JavaScript frameworks.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Q2: Multiple Choice (Single match)
        DB::table('questions')->insert([
            'id' => Str::uuid(),
            'quiz_id' => $quizId,
            'question_text' => 'What is the sum of 2 + 2?',
            'question_type' => 'MULTIPLE_CHOICE',
            'points' => 2,
            'order_index' => 2,
            'options' => json_encode(['3', '4', '5', '22']),
            'correct_answer' => '4',
            'explanation' => 'Basic arithmetic.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Q3: Short Answer (Manual Grading)
        DB::table('questions')->insert([
            'id' => Str::uuid(),
            'quiz_id' => $quizId,
            'question_text' => 'Explain why we use seeders in Laravel.',
            'question_type' => 'SHORT_ANSWER',
            'points' => 10,
            'order_index' => 3,
            'options' => null,
            'correct_answer' => 'Seeders are used to populate the database with test or initial data.',
            'explanation' => 'Seeders allow developers to quickly set up a database with reproducible data.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Q4: Essay (Manual Grading)
        DB::table('questions')->insert([
            'id' => Str::uuid(),
            'quiz_id' => $quizId,
            'question_text' => 'Describe your experience with Laravel Middleware.',
            'question_type' => 'ESSAY',
            'points' => 15,
            'order_index' => 4,
            'options' => null,
            'correct_answer' => 'Middleware provide a convenient mechanism for inspecting and filtering HTTP requests entering your application.',
            'explanation' => 'Middleware allows you to intercept requests and perform actions such as authentication or logging.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info('Questions added to Test Quiz.');

        // 4. Create a Sample Attempt (Graded) to verify Review UI
        $student = DB::table('users')->where('email', 'alice.student@example.com')->first();
        if ($student) {
            DB::table('quiz_attempts')->insert([
                'id' => Str::uuid(),
                'quiz_id' => $quizId,
                'student_id' => $student->id,
                'score' => 20, // Low score to check incorrect answers
                'is_passed' => false,
                'attempt_number' => 1,
                'time_spent' => 300,
                'started_at' => now()->subHours(1),
                'submitted_at' => now()->subMinutes(50),
                'created_at' => now()->subHours(1),
                'updated_at' => now()->subMinutes(50),
                'grading_status' => 'graded', // Force graded to show Correct Answers
            ]);
             $this->command->info("Created Sample Attempt for Alice (Graded).");
        }
    }
}
