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

        $tagVue = DB::table('tags')->where('slug', 'vue-js')->first();
        $tagLaravel = DB::table('tags')->where('slug', 'laravel')->first();
        $tagJS = DB::table('tags')->where('slug', 'javascript')->first();
        $tagWebDev = DB::table('tags')->where('slug', 'web-development')->first();
        if (!$tagVue || !$tagLaravel || !$tagJS || !$tagWebDev) {
            $this->command->error('Không tìm thấy Tags. Hãy chạy TagSeeder trước.');
            return;
        }

        // Prepare courses array first (do not insert yet, so we can extract tags for pivot)
        $courses = [
            [
                'id' => Str::uuid(),
                'title' => 'Complete Vue.js 3 Development Course',
                'slug' => 'complete-vuejs-3-development',
                'description' => 'Master Vue.js 3 from basics to advanced concepts...',
                'thumbnail' => 'https://images.unsplash.com/photo-1633356122544-f134324a6cee?w=400&h=300&fit=crop',
                'status' => 'PUBLISHED', 'level' => 'INTERMEDIATE', 'duration' => 1200,
                'price' => 99.99, 'passing_score' => 70, 'teacher_id' => $sarahTeacher->id,
                'created_at' => now(), 'updated_at' => now(), 'published_at' => now(),
                'tags' => [$tagVue->id, $tagJS->id, $tagWebDev->id] // Liên kết 3 tags
            ],
            [
                'id' => Str::uuid(),
                'title' => 'Laravel API Development Masterclass',
                'slug' => 'laravel-api-development-masterclass',
                'description' => 'Build powerful RESTful APIs with Laravel...',
                'thumbnail' => 'https://images.unsplash.com/photo-1555066931-4365d14bab1b?w=400&h=300&fit=crop',
                'status' => 'PUBLISHED', 'level' => 'ADVANCED', 'duration' => 1800,
                'price' => 149.99, 'passing_score' => 75, 'teacher_id' => $johnTeacher->id,
                'created_at' => now(), 'updated_at' => now(), 'published_at' => now(),
                'tags' => [$tagLaravel->id, $tagWebDev->id] // Liên kết 2 tags
            ],
            [
                'id' => Str::uuid(),
                'title' => 'Web Development Fundamentals',
                'slug' => 'web-development-fundamentals',
                'description' => 'Learn the basics of web development including HTML, CSS, JavaScript...',
                'thumbnail' => 'https://images.unsplash.com/photo-1517694712202-14dd9e38f757?w=400&h=300&fit=crop',
                'status' => 'PUBLISHED', 'level' => 'BEGINNER', 'duration' => 960,
                'price' => 49.99, 'passing_score' => 60, 'teacher_id' => $johnTeacher->id,
                'created_at' => now(), 'updated_at' => now(), 'published_at' => now(),
                'tags' => [$tagJS->id, $tagWebDev->id] // Liên kết 2 tags
            ],
            [
                'id' => Str::uuid(),
                'title' => 'Advanced JavaScript Patterns',
                'slug' => 'advanced-javascript-patterns',
                'description' => 'Deep dive into advanced JavaScript concepts...',
                'thumbnail' => 'https://images.unsplash.com/photo-1515879218367-8466d910aaa4?w=400&h=300&fit=crop',
                'status' => 'DRAFT', 'level' => 'EXPERT', 'duration' => 2400,
                'price' => 199.99, 'passing_score' => 80, 'teacher_id' => $sarahTeacher->id,
                'created_at' => now(), 'updated_at' => now(), 'published_at' => null,
                'tags' => [$tagJS->id] // Liên kết 1 tag
            ],
        ];

        // Build pivot rows and insert courses
        $courseTagPivots = [];
        foreach ($courses as $course) {
            // Thêm vào bảng 'course_tag'
            foreach ($course['tags'] as $tagId) {
                $courseTagPivots[] = [
                    'course_id' => $course['id'],
                    'tag_id' => $tagId,
                ];
            }
            // Xóa 'tags' key để chuẩn bị chèn vào bảng 'courses'
            unset($course['tags']);
            DB::table('courses')->insert($course);
        }

        // 5. Chèn dữ liệu vào bảng pivot (nếu có)
        if (!empty($courseTagPivots)) {
            DB::table('course_tag')->insert($courseTagPivots);
        }
    }
}