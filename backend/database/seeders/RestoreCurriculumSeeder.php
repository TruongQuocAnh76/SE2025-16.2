<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Course;

class RestoreCurriculumSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        optional($this->command)->info('Running RestoreCurriculumSeeder');

        // No large sample text by default. If you want to provide a multiline
        // curriculum text, set $sampleCurriculum below or enable structured mode.
        $sampleCurriculum = null;

        // Courses to attempt to update. Match by title; you may edit this list.
        $targets = [
            'Complete Vue.js 3 Development Course',
            'Complete Vue.js Development Course',
            'Vue.js Fundamentals (Module 1)'
        ];

        // New courses to create if missing. If 'curriculum' is omitted or null,
        // the course will be created with a NULL curriculum (no content).
        $newCourses = [
            [
                'title' => 'New Sample Course',
                'slug' => 'new-sample-course',
                'description' => 'Khóa học mẫu mới tạo bởi seeder',
                // 'curriculum' => null, // leave out or set null to keep curriculum NULL
            ],
        ];

        // If set to true (environment), the seeder will overwrite existing curricula.
        // Use: RESTORE_CURRICULUM_FORCE=true php artisan db:seed --class=RestoreCurriculumSeeder
        $force = filter_var(env('RESTORE_CURRICULUM_FORCE', false), FILTER_VALIDATE_BOOL);

        // If set to true, write a structured JSON curriculum where `modules` and `lessons`
        // keys exist and are non-null (empty arrays when there are none).
        // Use: RESTORE_CURRICULUM_STRUCTURED=true php artisan db:seed --class=RestoreCurriculumSeeder
        $structured = filter_var(env('RESTORE_CURRICULUM_STRUCTURED', false), FILTER_VALIDATE_BOOL);

        if ($structured) {
            $structuredCurriculum = [
                'title' => 'Khóa học mẫu: Thị giác máy tính',
                'description' => 'Nội dung tóm tắt cho khóa học mẫu',
                // Explicitly include modules and lessons as arrays (not null). Empty arrays mean none.
                'modules' => [],
                'lessons' => [],
                // Keep a textual summary for backward compatibility. If $sampleCurriculum is null,
                // provide a short placeholder summary.
                'raw_summary' => $sampleCurriculum ? trim(preg_replace('/\s+/', ' ', substr($sampleCurriculum, 0, 800))) : 'Nội dung sẽ được cập nhật sau.',
            ];

            // Encode as JSON string to store in the `curriculum` text column.
            $sampleCurriculum = json_encode($structuredCurriculum, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }

        DB::beginTransaction();
        try {
            // First: create any new courses requested.
            foreach ($newCourses as $nc) {
                $exists = Course::where('slug', $nc['slug'])->first();
                if ($exists) {
                    optional($this->command)->info("Course already exists: {$nc['slug']} (id: {$exists->id})");
                    continue;
                }

                $createData = [
                    'title' => $nc['title'],
                    'slug' => $nc['slug'],
                    'description' => $nc['description'] ?? null,
                    'curriculum' => $nc['curriculum'] ?? null,
                ];

                $created = Course::create($createData);
                if ($created) {
                    $msg = $created->curriculum ? 'with curriculum' : 'with NULL curriculum';
                    optional($this->command)->info("Created new course: {$created->title} (id: {$created->id}) {$msg}");
                }
            }

            // Then attempt to restore/update curricula for existing target titles.
            foreach ($targets as $title) {
                $course = Course::where('title', $title)->first();
                if (! $course) {
                    optional($this->command)->warn("Course not found: {$title}");
                    continue;
                }

                $existing = trim((string) $course->curriculum);

                // If there is already a reasonably long curriculum and --force not set, skip.
                if ($existing && ! $force) {
                    optional($this->command)->info("Skipping '{$title}' — existing curriculum present (id: {$course->id}). Use RESTORE_CURRICULUM_FORCE=true to overwrite.");
                    continue;
                }

                // If no sample curriculum is provided and not structured, leave as NULL and notify.
                if ($sampleCurriculum === null && ! $structured) {
                    $course->curriculum = null;
                    $course->save();
                    optional($this->command)->info("No curriculum provided for '{$title}'. Set to NULL (id: {$course->id}).");
                    continue;
                }

                // Otherwise set the sample/structured curriculum.
                $course->curriculum = $sampleCurriculum;
                $course->save();
                optional($this->command)->info("Restored curriculum for: {$title} (id: {$course->id})");
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            optional($this->command)->error('RestoreCurriculumSeeder failed: ' . $e->getMessage());
            throw $e;
        }

        optional($this->command)->info('RestoreCurriculumSeeder completed.');
    }
}
