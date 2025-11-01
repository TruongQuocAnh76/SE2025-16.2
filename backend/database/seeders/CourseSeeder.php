<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get teacher users
        $johnTeacher = DB::table('users')->where('email', 'john.teacher@certchain.com')->first();
        $sarahTeacher = DB::table('users')->where('email', 'sarah.johnson@certchain.com')->first();

        // Thoát nếu không tìm thấy giáo viên (để tránh lỗi)
        if (!$johnTeacher || !$sarahTeacher) {
            $this->command->error('Không tìm thấy giáo viên. Hãy chạy UserSeeder trước.');
            return;
        }

        DB::table('courses')->insert([
            [
                'id' => Str::uuid(),
                'title' => 'Complete Vue.js 3 Development Course',
                'slug' => 'complete-vuejs-3-development',
                'description' => 'Master Vue.js 3 from basics to advanced concepts including Composition API, Pinia state management, and modern development practices.',
                // SỬA LỖI: Dùng URL ảnh thật
                'thumbnail' => 'https://images.unsplash.com/photo-1633356122544-f134324a6cee?w=400&h=300&fit=crop',
                'status' => 'PUBLISHED',
                'level' => 'INTERMEDIATE',
                'duration' => 1200, // 20 hours in minutes
                'price' => 99.99,
                'passing_score' => 70,
                // 'required_completion' => 90, // Tạm thời comment nếu cột này không tồn tại
                'teacher_id' => $sarahTeacher->id,
                'created_at' => now(),
                'updated_at' => now(),
                'published_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'title' => 'Laravel API Development Masterclass',
                'slug' => 'laravel-api-development-masterclass',
                'description' => 'Build powerful RESTful APIs with Laravel including authentication, testing, deployment, and best practices.',
                // SỬA LỖI: Dùng URL ảnh thật
                'thumbnail' => 'https://images.unsplash.com/photo-1555066931-4365d14bab1b?w=400&h=300&fit=crop',
                'status' => 'PUBLISHED',
                'level' => 'ADVANCED',
                'duration' => 1800, // 30 hours in minutes
                'price' => 149.99,
                'passing_score' => 75,
                // 'required_completion' => 95,
                'teacher_id' => $johnTeacher->id,
                'created_at' => now(),
                'updated_at' => now(),
                'published_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'title' => 'Web Development Fundamentals',
                'slug' => 'web-development-fundamentals',
                'description' => 'Learn the basics of web development including HTML, CSS, JavaScript, and fundamental programming concepts.',
                // SỬA LỖI: Dùng URL ảnh thật
                'thumbnail' => 'https://images.unsplash.com/photo-1517694712202-14dd9e38f757?w=400&h=300&fit=crop',
                'status' => 'PUBLISHED',
                'level' => 'BEGINNER',
                'duration' => 960, // 16 hours in minutes
                'price' => 49.99,
                'passing_score' => 60,
                // 'required_completion' => 80,
                'teacher_id' => $johnTeacher->id,
                'created_at' => now(),
                'updated_at' => now(),
                'published_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'title' => 'Advanced JavaScript Patterns',
                'slug' => 'advanced-javascript-patterns',
                'description' => 'Deep dive into advanced JavaScript concepts, design patterns, and modern ES6+ features.',
                // SỬA LỖI: Dùng URL ảnh thật
                'thumbnail' => 'https://images.unsplash.com/photo-1515879218367-8466d910aaa4?w=400&h=300&fit=crop',
                'status' => 'DRAFT',
                'level' => 'EXPERT',
                'duration' => 2400, // 40 hours in minutes
                'price' => 199.99,
                'passing_score' => 80,
                // 'required_completion' => 100,
                'teacher_id' => $sarahTeacher->id,
                'created_at' => now(),
                'updated_at' => now(),
                'published_at' => null,
            ],
        ]);
    }
}