<?php namespace App\Services;
use App\Repositories\ProgressRepository;
use App\Repositories\EnrollmentRepository;
use App\Models\Lesson;
use App\Models\Module;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ProgressService {
    protected $progressRepository;
    protected $enrollmentRepository;
    public function __construct(ProgressRepository $progressRepository, EnrollmentRepository $enrollmentRepository) {
        $this->progressRepository = $progressRepository;
        $this->enrollmentRepository = $enrollmentRepository;
    }
    public function getCourseProgressDetail($studentId, $courseId) {
        $modules = Module::where('course_id', $courseId)->with(['lessons' => function ($query) use ($studentId) {
            $query->leftJoin('progress', function ($join) use ($studentId) {
                $join->on('lessons.id', '=', 'progress.lesson_id')->where('progress.student_id', '=', $studentId);
            })->select('lessons.id', 'lessons.title', 'lessons.order_index', 'lessons.content_type', 'lessons.duration', DB::raw('COALESCE(progress.is_completed, false) as is_completed'), DB::raw('COALESCE(progress.time_spent, 0) as time_spent'))->orderBy('lessons.order_index');
        }])->orderBy('order_index')->get();
        $totalLessons = Lesson::whereIn('module_id', $modules->pluck('id'))->count();
        $completedLessons = $this->progressRepository->getByStudentId($studentId)->whereIn('lesson_id', Lesson::whereIn('module_id', $modules->pluck('id'))->pluck('id'))->where('is_completed', true)->count();
        $progressPercent = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100) : 0;
        $this->enrollmentRepository->update($this->enrollmentRepository->getByStudentId($studentId)->where('course_id', $courseId)->first()->id, ['progress' => $progressPercent]);
        return ['course_id' => $courseId, 'modules' => $modules, 'progress_percent' => $progressPercent];
    }
    public function markLessonCompleted($studentId, $lessonId) {
        $data = ['id' => Str::uuid(), 'student_id' => $studentId, 'lesson_id' => $lessonId, 'is_completed' => true, 'completed_at' => now()];
        return $this->progressRepository->updateOrCreate(['student_id' => $studentId, 'lesson_id' => $lessonId], $data);
    }
    public function updateTimeSpent($studentId, $lessonId, $timeSpent) {
        return $this->progressRepository->updateOrCreate(['student_id' => $studentId, 'lesson_id' => $lessonId], ['time_spent' => $timeSpent, 'last_accessed_at' => now()]);
    }
}
