<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReviewSeeder extends Seeder
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

        // Get courses
        $vueCourse = DB::table('courses')->where('slug', 'complete-vuejs-3-development')->first();
        $laravelCourse = DB::table('courses')->where('slug', 'laravel-api-development-masterclass')->first();
        $webFundamentals = DB::table('courses')->where('slug', 'web-development-fundamentals')->first();

        DB::table('reviews')->insert([
            [
                'id' => Str::uuid(),
                'rating' => 5,
                'comment' => 'Excellent course! The instructor explains Vue.js concepts very clearly and the hands-on projects really helped me understand the framework. Highly recommended for anyone looking to learn Vue.js.',
                'student_id' => $alice->id,
                'course_id' => $vueCourse->id,
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subDays(3),
            ],
            [
                'id' => Str::uuid(),
                'rating' => 4,
                'comment' => 'Great course for beginners. The content is well-structured and easy to follow. I feel much more confident about web development now.',
                'student_id' => $alice->id,
                'course_id' => $webFundamentals->id,
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(5),
            ],
            [
                'id' => Str::uuid(),
                'rating' => 3,
                'comment' => 'The course content is good but could use more practical examples. Some concepts were explained too quickly for a beginner.',
                'student_id' => $bob->id,
                'course_id' => $webFundamentals->id,
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ],
            [
                'id' => Str::uuid(),
                'rating' => 5,
                'comment' => 'Outstanding Laravel course! John really knows his stuff. The API development section was particularly valuable for my current project.',
                'student_id' => $charlie->id,
                'course_id' => $laravelCourse->id,
                'created_at' => now()->subDays(1),
                'updated_at' => now()->subDays(1),
            ],
            [
                'id' => Str::uuid(),
                'rating' => 4,
                'comment' => 'Good introduction to Vue.js. The component section could be expanded with more complex examples, but overall a solid course.',
                'student_id' => $charlie->id,
                'course_id' => $vueCourse->id,
                'created_at' => now()->subHours(12),
                'updated_at' => now()->subHours(12),
            ],
        ]);
    }
}
