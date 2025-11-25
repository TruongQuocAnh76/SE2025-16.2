<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Course;
use Carbon\Carbon;

class ResetCoursesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        optional($this->command)->info('Running ResetCoursesSeeder');

        // Criteria (can be set via env vars)
        $slugPrefix = env('RESET_COURSES_SLUG_PREFIX', 'test-');
        $titleContains = env('RESET_COURSES_TITLE_CONTAINS', 'Test');
        $since = env('RESET_COURSES_SINCE', null); // e.g. '2025-11-01'

        $force = filter_var(env('RESET_COURSES_FORCE', false), FILTER_VALIDATE_BOOL);

        // Build query of candidates
        $query = Course::query();

        $query->where(function ($q) use ($slugPrefix, $titleContains, $since) {
            if ($slugPrefix) {
                $q->orWhere('slug', 'like', $slugPrefix . '%');
            }
            if ($titleContains) {
                $q->orWhere('title', 'like', '%' . $titleContains . '%');
            }
            if ($since) {
                try {
                    $dt = Carbon::parse($since);
                    $q->orWhere('created_at', '>=', $dt->toDateTimeString());
                } catch (\Throwable $e) {
                    // ignore parse errors
                }
            }
        });

        $candidates = $query->get();

        if ($candidates->isEmpty()) {
            optional($this->command)->info('No candidate courses found for the given criteria. Nothing to do.');
            return;
        }

        optional($this->command)->info('Found ' . $candidates->count() . ' candidate(s) to remove:');
        foreach ($candidates as $c) {
            optional($this->command)->line(" - [id={$c->id}] {$c->title} (slug={$c->slug}) created_at={$c->created_at}");
        }

        if (! $force) {
            optional($this->command)->warn('Preview only. To actually delete these courses, set env RESET_COURSES_FORCE=true and re-run this seeder.');
            return;
        }

        // Proceed with backup and deletion
        DB::beginTransaction();
        try {
            foreach ($candidates as $course) {
                // Backup course data
                DB::table('course_backups')->insert([
                    'course_id' => $course->id,
                    'data' => json_encode($course->toArray(), JSON_UNESCAPED_UNICODE),
                    'backed_up_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Delete via model to trigger relations/cascade logic
                $course->delete();
                optional($this->command)->info("Deleted course id={$course->id} title='{$course->title}' (backup saved)");
            }

            DB::commit();
            optional($this->command)->info('ResetCoursesSeeder completed successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            optional($this->command)->error('ResetCoursesSeeder failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
