<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EnrollmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get student users
        $alice = DB::table('users')->where('email', 'alice.student@example.com')->first();
        $bob = DB::table('users')->where('email', 'bob.learner@example.com')->first();
        $charlie = DB::table('users')->where('email', 'charlie.dev@example.com')->first();

        // Get courses
        $vueCourse = DB::table('courses')->where('slug', 'complete-vuejs-3-development')->first();
        $laravelCourse = DB::table('courses')->where('slug', 'laravel-api-development-masterclass')->first();
        $webFundamentals = DB::table('courses')->where('slug', 'web-development-fundamentals')->first();

        DB::table('enrollments')->insert([
            [
                'id' => Str::uuid(),
                'student_id' => $alice->id,
                'course_id' => $vueCourse->id,
                'status' => 'ACTIVE',
                'progress' => 45,
                'enrolled_at' => now()->subDays(15),
                'completed_at' => null,
                'expires_at' => null,
                'created_at' => now()->subDays(15),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'student_id' => $alice->id,
                'course_id' => $webFundamentals->id,
                'status' => 'COMPLETED',
                'progress' => 100,
                'enrolled_at' => now()->subDays(30),
                'completed_at' => now()->subDays(5),
                'expires_at' => null,
                'created_at' => now()->subDays(30),
                'updated_at' => now()->subDays(5),
            ],
            [
                'id' => Str::uuid(),
                'student_id' => $bob->id,
                'course_id' => $webFundamentals->id,
                'status' => 'ACTIVE',
                'progress' => 25,
                'enrolled_at' => now()->subDays(10),
                'completed_at' => null,
                'expires_at' => null,
                'created_at' => now()->subDays(10),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'student_id' => $charlie->id,
                'course_id' => $laravelCourse->id,
                'status' => 'ACTIVE',
                'progress' => 60,
                'enrolled_at' => now()->subDays(20),
                'completed_at' => null,
                'expires_at' => null,
                'created_at' => now()->subDays(20),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'student_id' => $charlie->id,
                'course_id' => $vueCourse->id,
                'status' => 'ACTIVE',
                'progress' => 15,
                'enrolled_at' => now()->subDays(5),
                'completed_at' => null,
                'expires_at' => null,
                'created_at' => now()->subDays(5),
                'updated_at' => now(),
            ],
        ]);
    }
}
