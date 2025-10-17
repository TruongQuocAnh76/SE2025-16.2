<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QuizSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get courses
        $vueCourse = DB::table('courses')->where('slug', 'complete-vuejs-3-development')->first();
        $laravelCourse = DB::table('courses')->where('slug', 'laravel-api-development-masterclass')->first();
        $webFundamentals = DB::table('courses')->where('slug', 'web-development-fundamentals')->first();

        DB::table('quizzes')->insert([
            [
                'id' => Str::uuid(),
                'title' => 'Vue.js Fundamentals Quiz',
                'description' => 'Test your understanding of Vue.js basic concepts and syntax.',
                'quiz_type' => 'GRADED',
                'time_limit' => 30,
                'passing_score' => 70,
                'max_attempts' => 3,
                'order_index' => 1,
                'is_active' => true,
                'course_id' => $vueCourse->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'title' => 'Vue.js Components Assessment',
                'description' => 'Evaluate your knowledge of Vue.js components and communication.',
                'quiz_type' => 'GRADED',
                'time_limit' => 45,
                'passing_score' => 75,
                'max_attempts' => 2,
                'order_index' => 2,
                'is_active' => true,
                'course_id' => $vueCourse->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'title' => 'Vue.js Final Exam',
                'description' => 'Comprehensive final examination covering all Vue.js topics.',
                'quiz_type' => 'FINAL',
                'time_limit' => 90,
                'passing_score' => 80,
                'max_attempts' => 1,
                'order_index' => 3,
                'is_active' => true,
                'course_id' => $vueCourse->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'title' => 'Laravel Basics Quiz',
                'description' => 'Test your understanding of Laravel fundamentals.',
                'quiz_type' => 'PRACTICE',
                'time_limit' => null,
                'passing_score' => 60,
                'max_attempts' => null,
                'order_index' => 1,
                'is_active' => true,
                'course_id' => $laravelCourse->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'title' => 'Web Development Knowledge Check',
                'description' => 'Quick knowledge check for web development basics.',
                'quiz_type' => 'PRACTICE',
                'time_limit' => 20,
                'passing_score' => 50,
                'max_attempts' => null,
                'order_index' => 1,
                'is_active' => true,
                'course_id' => $webFundamentals->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'title' => 'Web Development Final Test',
                'description' => 'Final assessment for web development fundamentals course.',
                'quiz_type' => 'FINAL',
                'time_limit' => 60,
                'passing_score' => 70,
                'max_attempts' => 2,
                'order_index' => 2,
                'is_active' => true,
                'course_id' => $webFundamentals->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
