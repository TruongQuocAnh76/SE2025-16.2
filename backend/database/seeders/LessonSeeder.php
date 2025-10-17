<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LessonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get module IDs
        $vueFundamentals = DB::table('modules')->where('title', 'Vue.js Fundamentals')->first();
        $vueComponents = DB::table('modules')->where('title', 'Component Deep Dive')->first();
        $htmlCssModule = DB::table('modules')->where('title', 'HTML & CSS Basics')->first();
        $jsModule = DB::table('modules')->where('title', 'JavaScript Introduction')->first();

        DB::table('lessons')->insert([
            // Vue.js Fundamentals Module Lessons
            [
                'id' => Str::uuid(),
                'title' => 'What is Vue.js?',
                'description' => 'Introduction to Vue.js framework and its benefits.',
                'content_type' => 'VIDEO',
                'content_url' => 'https://example.com/videos/vue-intro.mp4',
                'text_content' => null,
                'duration' => 600, // 10 minutes
                'order_index' => 1,
                'is_free' => true,
                'module_id' => $vueFundamentals->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'title' => 'Setting Up Your Development Environment',
                'description' => 'Installing Node.js, Vue CLI, and setting up your first project.',
                'content_type' => 'VIDEO',
                'content_url' => 'https://example.com/videos/vue-setup.mp4',
                'text_content' => null,
                'duration' => 900, // 15 minutes
                'order_index' => 2,
                'is_free' => false,
                'module_id' => $vueFundamentals->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'title' => 'Reactive Data and Templates',
                'description' => 'Understanding Vue\'s reactivity system and template syntax.',
                'content_type' => 'VIDEO',
                'content_url' => 'https://example.com/videos/vue-reactivity.mp4',
                'text_content' => null,
                'duration' => 1200, // 20 minutes
                'order_index' => 3,
                'is_free' => false,
                'module_id' => $vueFundamentals->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'title' => 'Vue.js Cheat Sheet',
                'description' => 'Quick reference guide for Vue.js syntax and methods.',
                'content_type' => 'PDF',
                'content_url' => 'https://example.com/pdfs/vue-cheatsheet.pdf',
                'text_content' => null,
                'duration' => null,
                'order_index' => 4,
                'is_free' => false,
                'module_id' => $vueFundamentals->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Component Deep Dive Module Lessons
            [
                'id' => Str::uuid(),
                'title' => 'Creating Your First Component',
                'description' => 'Learn how to create and use Vue components.',
                'content_type' => 'VIDEO',
                'content_url' => 'https://example.com/videos/vue-components.mp4',
                'text_content' => null,
                'duration' => 1500, // 25 minutes
                'order_index' => 1,
                'is_free' => false,
                'module_id' => $vueComponents->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'title' => 'Props and Component Communication',
                'description' => 'Passing data between parent and child components.',
                'content_type' => 'VIDEO',
                'content_url' => 'https://example.com/videos/vue-props.mp4',
                'text_content' => null,
                'duration' => 1800, // 30 minutes
                'order_index' => 2,
                'is_free' => false,
                'module_id' => $vueComponents->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // HTML & CSS Basics Module Lessons
            [
                'id' => Str::uuid(),
                'title' => 'HTML Structure and Semantics',
                'description' => 'Understanding HTML document structure and semantic elements.',
                'content_type' => 'VIDEO',
                'content_url' => 'https://example.com/videos/html-basics.mp4',
                'text_content' => null,
                'duration' => 1200, // 20 minutes
                'order_index' => 1,
                'is_free' => true,
                'module_id' => $htmlCssModule->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'title' => 'CSS Selectors and Properties',
                'description' => 'Learn CSS selectors, properties, and basic styling techniques.',
                'content_type' => 'VIDEO',
                'content_url' => 'https://example.com/videos/css-basics.mp4',
                'text_content' => null,
                'duration' => 1500, // 25 minutes
                'order_index' => 2,
                'is_free' => false,
                'module_id' => $htmlCssModule->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'title' => 'Responsive Design Principles',
                'description' => 'Creating responsive layouts with CSS Grid and Flexbox.',
                'content_type' => 'TEXT',
                'content_url' => null,
                'text_content' => 'Responsive design is essential for modern web development. In this lesson, we\'ll explore CSS Grid and Flexbox to create layouts that work on all devices...',
                'duration' => null,
                'order_index' => 3,
                'is_free' => false,
                'module_id' => $htmlCssModule->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // JavaScript Introduction Module Lessons
            [
                'id' => Str::uuid(),
                'title' => 'JavaScript Variables and Data Types',
                'description' => 'Understanding JavaScript variables, primitives, and objects.',
                'content_type' => 'VIDEO',
                'content_url' => 'https://example.com/videos/js-variables.mp4',
                'text_content' => null,
                'duration' => 1800, // 30 minutes
                'order_index' => 1,
                'is_free' => true,
                'module_id' => $jsModule->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'title' => 'Functions and Scope',
                'description' => 'Learn about JavaScript functions, parameters, and scope.',
                'content_type' => 'VIDEO',
                'content_url' => 'https://example.com/videos/js-functions.mp4',
                'text_content' => null,
                'duration' => 2100, // 35 minutes
                'order_index' => 2,
                'is_free' => false,
                'module_id' => $jsModule->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
