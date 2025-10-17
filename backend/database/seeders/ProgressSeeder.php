<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProgressSeeder extends Seeder
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

        // Get some lessons
        $vueIntro = DB::table('lessons')->where('title', 'What is Vue.js?')->first();
        $vueSetup = DB::table('lessons')->where('title', 'Setting Up Your Development Environment')->first();
        $vueReactivity = DB::table('lessons')->where('title', 'Reactive Data and Templates')->first();
        $htmlStructure = DB::table('lessons')->where('title', 'HTML Structure and Semantics')->first();
        $cssSelectors = DB::table('lessons')->where('title', 'CSS Selectors and Properties')->first();
        $jsVariables = DB::table('lessons')->where('title', 'JavaScript Variables and Data Types')->first();

        DB::table('progress')->insert([
            // Alice's progress
            [
                'id' => Str::uuid(),
                'student_id' => $alice->id,
                'lesson_id' => $vueIntro->id,
                'is_completed' => true,
                'time_spent' => 650,
                'last_accessed_at' => now()->subDays(10),
                'completed_at' => now()->subDays(10),
                'created_at' => now()->subDays(15),
                'updated_at' => now()->subDays(10),
            ],
            [
                'id' => Str::uuid(),
                'student_id' => $alice->id,
                'lesson_id' => $vueSetup->id,
                'is_completed' => true,
                'time_spent' => 920,
                'last_accessed_at' => now()->subDays(8),
                'completed_at' => now()->subDays(8),
                'created_at' => now()->subDays(12),
                'updated_at' => now()->subDays(8),
            ],
            [
                'id' => Str::uuid(),
                'student_id' => $alice->id,
                'lesson_id' => $vueReactivity->id,
                'is_completed' => false,
                'time_spent' => 480,
                'last_accessed_at' => now()->subDays(2),
                'completed_at' => null,
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(2),
            ],
            [
                'id' => Str::uuid(),
                'student_id' => $alice->id,
                'lesson_id' => $htmlStructure->id,
                'is_completed' => true,
                'time_spent' => 1250,
                'last_accessed_at' => now()->subDays(25),
                'completed_at' => now()->subDays(25),
                'created_at' => now()->subDays(30),
                'updated_at' => now()->subDays(25),
            ],
            [
                'id' => Str::uuid(),
                'student_id' => $alice->id,
                'lesson_id' => $cssSelectors->id,
                'is_completed' => true,
                'time_spent' => 1580,
                'last_accessed_at' => now()->subDays(20),
                'completed_at' => now()->subDays(20),
                'created_at' => now()->subDays(25),
                'updated_at' => now()->subDays(20),
            ],

            // Bob's progress
            [
                'id' => Str::uuid(),
                'student_id' => $bob->id,
                'lesson_id' => $htmlStructure->id,
                'is_completed' => true,
                'time_spent' => 1350,
                'last_accessed_at' => now()->subDays(8),
                'completed_at' => now()->subDays(8),
                'created_at' => now()->subDays(10),
                'updated_at' => now()->subDays(8),
            ],
            [
                'id' => Str::uuid(),
                'student_id' => $bob->id,
                'lesson_id' => $cssSelectors->id,
                'is_completed' => false,
                'time_spent' => 720,
                'last_accessed_at' => now()->subDays(1),
                'completed_at' => null,
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subDays(1),
            ],

            // Charlie's progress
            [
                'id' => Str::uuid(),
                'student_id' => $charlie->id,
                'lesson_id' => $vueIntro->id,
                'is_completed' => true,
                'time_spent' => 580,
                'last_accessed_at' => now()->subDays(4),
                'completed_at' => now()->subDays(4),
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(4),
            ],
            [
                'id' => Str::uuid(),
                'student_id' => $charlie->id,
                'lesson_id' => $jsVariables->id,
                'is_completed' => true,
                'time_spent' => 1920,
                'last_accessed_at' => now()->subDays(15),
                'completed_at' => now()->subDays(15),
                'created_at' => now()->subDays(18),
                'updated_at' => now()->subDays(15),
            ],
        ]);
    }
}
