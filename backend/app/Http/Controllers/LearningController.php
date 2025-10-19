<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\Progress;
use App\Models\Module;
use App\Models\Course;

class LearningController extends Controller
{
    /**
     * 📘 Lấy danh sách bài học (và tiến độ) trong khóa học của học viên
     */
    public function getCourseProgress($courseId)
    {
        $studentId = Auth::id();

        // Kiểm tra đã đăng ký khóa học chưa
        $enrollment = Enrollment::where('course_id', $courseId)
            ->where('student_id', $studentId)
            ->first();

        if (!$enrollment) {
            return response()->json(['error' => 'Not enrolled in this course'], 403);
        }

        // Lấy modules + lessons kèm trạng thái hoàn thành
        $modules = Module::where('course_id', $courseId)
            ->with(['lessons' => function ($query) use ($studentId) {
                $query->leftJoin('progress', function ($join) use ($studentId) {
                    $join->on('lessons.id', '=', 'progress.lesson_id')
                         ->where('progress.student_id', '=', $studentId);
                })
                ->select(
                    'lessons.id',
                    'lessons.title',
                    'lessons.order_index',
                    'lessons.content_type',
                    'lessons.duration',
                    DB::raw('IFNULL(progress.is_completed, 0) as is_completed'),
                    DB::raw('IFNULL(progress.time_spent, 0) as time_spent')
                )
                ->orderBy('lessons.order_index');
            }])
            ->orderBy('order_index')
            ->get();

        // Tính toán tiến độ
        $totalLessons = Lesson::whereIn('module_id', $modules->pluck('id'))->count();
        $completedLessons = Progress::where('student_id', $studentId)
            ->whereIn('lesson_id', Lesson::whereIn('module_id', $modules->pluck('id'))->pluck('id'))
            ->where('is_completed', true)
            ->count();

        $progressPercent = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100) : 0;

        // Cập nhật bảng enrollments
        $enrollment->update(['progress' => $progressPercent]);

        return response()->json([
            'course_id' => $courseId,
            'modules' => $modules,
            'progress_percent' => $progressPercent,
        ]);
    }

    /**
     * ✅ Đánh dấu bài học đã hoàn thành
     */
    public function markLessonCompleted($lessonId)
    {
        $studentId = Auth::id();

        $lesson = Lesson::findOrFail($lessonId);
        $courseId = $lesson->module->course_id;

        // Kiểm tra enrollment
        $enrollment = Enrollment::where('course_id', $courseId)
            ->where('student_id', $studentId)
            ->first();

        if (!$enrollment) {
            return response()->json(['error' => 'You are not enrolled in this course'], 403);
        }

        // Update hoặc tạo mới progress
        $progress = Progress::updateOrCreate(
            [
                'student_id' => $studentId,
                'lesson_id' => $lessonId,
            ],
            [
                'is_completed' => true,
                'completed_at' => now(),
            ]
        );

        // Tính lại % tổng
        $this->updateCourseProgress($courseId, $studentId);

        return response()->json(['message' => 'Lesson marked as completed', 'progress' => $progress]);
    }

    /**
     * ⏱️ Cập nhật thời gian học 1 bài
     */
    public function updateTimeSpent(Request $request, $lessonId)
    {
        $studentId = Auth::id();

        $validated = $request->validate([
            'time_spent' => 'required|integer|min:1',
        ]);

        $lesson = Lesson::findOrFail($lessonId);
        $courseId = $lesson->module->course_id;

        // Kiểm tra enrollment
        $enrollment = Enrollment::where('course_id', $courseId)
            ->where('student_id', $studentId)
            ->first();

        if (!$enrollment) {
            return response()->json(['error' => 'Not enrolled in this course'], 403);
        }

        $progress = Progress::updateOrCreate(
            [
                'student_id' => $studentId,
                'lesson_id' => $lessonId,
            ],
            [
                'time_spent' => DB::raw("time_spent + {$validated['time_spent']}"),
                'last_accessed_at' => now(),
            ]
        );

        return response()->json(['message' => 'Progress time updated', 'progress' => $progress]);
    }

    /**
     * 📊 Cập nhật % tiến độ khóa học
     */
    private function updateCourseProgress($courseId, $studentId)
    {
        $totalLessons = Lesson::whereIn(
            'module_id',
            Module::where('course_id', $courseId)->pluck('id')
        )->count();

        $completedLessons = Progress::where('student_id', $studentId)
            ->whereIn('lesson_id', Lesson::whereIn('module_id', Module::where('course_id', $courseId)->pluck('id'))->pluck('id'))
            ->where('is_completed', true)
            ->count();

        $progressPercent = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100) : 0;

        Enrollment::where('course_id', $courseId)
            ->where('student_id', $studentId)
            ->update(['progress' => $progressPercent]);

        return $progressPercent;
    }

    /**
     * 🏁 Hoàn thành toàn khóa học (nếu đủ điều kiện)
     */
    public function completeCourse($courseId)
    {
        $studentId = Auth::id();

        $enrollment = Enrollment::where('course_id', $courseId)
            ->where('student_id', $studentId)
            ->firstOrFail();

        if ($enrollment->progress < 100) {
            return response()->json(['error' => 'You have not completed all lessons yet'], 400);
        }

        $enrollment->update([
            'status' => 'COMPLETED',
            'completed_at' => now(),
        ]);

        return response()->json(['message' => 'Course completed successfully']);
    }
}
