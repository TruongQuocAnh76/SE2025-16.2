<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get course IDs
        $vueCourse = DB::table('courses')->where('slug', 'complete-vuejs-3-development')->first();
        $laravelCourse = DB::table('courses')->where('slug', 'laravel-api-development-masterclass')->first();
        $webFundamentals = DB::table('courses')->where('slug', 'web-development-fundamentals')->first();

        DB::table('modules')->insert([
            // Vue.js Course Modules
            [
                'id' => Str::uuid(),
                'title' => 'Vue.js Fundamentals',
                'description' => 'Learn the basics of Vue.js including reactive data, templates, and component basics.',
                'order_index' => 1,
                'course_id' => $vueCourse->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'title' => 'Component Deep Dive',
                'description' => 'Advanced component techniques, props, events, and component communication.',
                'order_index' => 2,
                'course_id' => $vueCourse->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'title' => 'State Management with Pinia',
                'description' => 'Learn modern state management using Pinia store.',
                'order_index' => 3,
                'course_id' => $vueCourse->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Laravel Course Modules
            [
                'id' => Str::uuid(),
                'title' => 'Laravel Fundamentals',
                'description' => 'Core Laravel concepts, routing, controllers, and middleware.',
                'order_index' => 1,
                'course_id' => $laravelCourse->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'title' => 'Database & Eloquent ORM',
                'description' => 'Database design, migrations, relationships, and advanced Eloquent features.',
                'order_index' => 2,
                'course_id' => $laravelCourse->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'title' => 'API Development & Authentication',
                'description' => 'Building RESTful APIs with authentication and authorization.',
                'order_index' => 3,
                'course_id' => $laravelCourse->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Web Fundamentals Course Modules
            [
                'id' => Str::uuid(),
                'title' => 'HTML & CSS Basics',
                'description' => 'Fundamentals of HTML structure and CSS styling.',
                'order_index' => 1,
                'course_id' => $webFundamentals->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'title' => 'JavaScript Introduction',
                'description' => 'Basic JavaScript programming concepts and DOM manipulation.',
                'order_index' => 2,
                'course_id' => $webFundamentals->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'title' => 'Building Your First Website',
                'description' => 'Put it all together to create a complete website project.',
                'order_index' => 3,
                'course_id' => $webFundamentals->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
