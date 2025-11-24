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
use App\Jobs\IssueCertificateToBlockchain;

/**
 * @OA\Server(
 *     url="http://localhost:8000/api",
 *     description="Local development server"
 * )
 */
class LearningController extends Controller
{
    /**
     * @OA\Get(
     *     path="/learning/course/{courseId}",
     *     summary="Get course progress for student",
     *     tags={"Learning"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="courseId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Course progress with lessons",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(response=403, description="Not enrolled in course"),
     *     @OA\Response(response=404, description="Course not found")
     * )
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
                    DB::raw('COALESCE(progress.is_completed, false) as is_completed'),
                    DB::raw('COALESCE(progress.time_spent, 0) as time_spent')
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
     * @OA\Post(
     *     path="/learning/lesson/{lessonId}/complete",
     *     summary="Mark lesson as completed",
     *     description="Mark a lesson as completed for the authenticated student",
     *     operationId="markLessonCompleted",
     *     tags={"Learning"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="lessonId",
     *         in="path",
     *         required=true,
     *         description="Lesson ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lesson marked as completed",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="progress", ref="#/components/schemas/Progress")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Not enrolled in course",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Lesson not found"
     *     )
     * )
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
     * @OA\Post(
     *     path="/learning/lesson/{lessonId}/time",
     *     summary="Update time spent on lesson",
     *     description="Update the time spent studying a lesson",
     *     operationId="updateTimeSpent",
     *     tags={"Learning"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="lessonId",
     *         in="path",
     *         required=true,
     *         description="Lesson ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"time_spent"},
     *             @OA\Property(property="time_spent", type="integer", minimum=1, description="Time spent in seconds", example=300)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Time updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="progress", ref="#/components/schemas/Progress")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Not enrolled in course",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Lesson not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
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
                'last_accessed_at' => now(),
            ]
        );

        // Increment time_spent regardless of new or existing
        $progress->increment('time_spent', $validated['time_spent']);

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

        // Update enrollment progress
        $enrollment = Enrollment::where('course_id', $courseId)
            ->where('student_id', $studentId)
            ->first();

        if ($enrollment) {
            $enrollment->update(['progress' => $progressPercent]);

            // If course is 100% complete, dispatch certificate issuance job
            if ($progressPercent == 100 && !$enrollment->completed_at) {
                $enrollment->update(['completed_at' => now()]);
                
                // Check if certificate already exists
                $existingCertificate = \App\Models\Certificate::where([
                    'student_id' => $studentId,
                    'course_id' => $courseId
                ])->first();
                
                if (!$existingCertificate) {
                    // Create certificate
                    $certificate = \App\Models\Certificate::create([
                        'id' => Str::uuid(),
                        'student_id' => $studentId,
                        'course_id' => $courseId,
                        'certificate_number' => 'CERT-' . strtoupper(Str::random(8)),
                        'issued_at' => now(),
                        'status' => 'PENDING',
                        'final_score' => 100.00
                    ]);

                    // Dispatch job to issue certificate to blockchain
                    IssueCertificateToBlockchain::dispatch($studentId, $courseId);
                }
            }
        }

        return $progressPercent;
    }

    /**
     * @OA\Post(
     *     path="/learning/course/{courseId}/complete",
     *     summary="Complete course",
     *     description="Mark the entire course as completed (requires all lessons to be completed)",
     *     operationId="completeCourse",
     *     tags={"Learning"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="courseId",
     *         in="path",
     *         required=true,
     *         description="Course ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Course completed successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Not all lessons completed",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Course not found or not enrolled"
     *     )
     * )
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

    /**
     * @OA\Get(
     *     path="/learning/student/{studentId}/courses/time-spent",
     *     summary="Get total time spent on each enrolled course for a student",
     *     tags={"Learning"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="studentId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Time spent on each enrolled course",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="course_id", type="string", format="uuid"),
     *                 @OA\Property(property="course_title", type="string"),
     *                 @OA\Property(property="total_time_spent", type="integer", description="Total time spent in seconds"),
     *                 @OA\Property(property="enrollment_status", type="string"),
     *                 @OA\Property(property="enrolled_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Student not found")
     * )
     */
    public function getCoursesTimeSpent($studentId)
    {
        $currentUser = Auth::user();

        // Check if user can view this student's data
        if ($currentUser->id !== $studentId && !in_array($currentUser->role, ['ADMIN', 'TEACHER'])) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        // Verify student exists
        $student = \App\Models\User::findOrFail($studentId);

        $coursesTimeSpent = DB::table('enrollments')
            ->join('courses', 'enrollments.course_id', '=', 'courses.id')
            ->leftJoin('modules', 'courses.id', '=', 'modules.course_id')
            ->leftJoin('lessons', 'modules.id', '=', 'lessons.module_id')
            ->leftJoin('progress', function($join) use ($studentId) {
                $join->on('progress.lesson_id', '=', 'lessons.id')
                     ->where('progress.student_id', '=', $studentId);
            })
            ->where('enrollments.student_id', $studentId)
            ->select(
                'courses.id as course_id',
                'courses.title as course_title',
                'enrollments.status as enrollment_status',
                'enrollments.enrolled_at',
                DB::raw('COALESCE(SUM(progress.time_spent), 0) as total_time_spent')
            )
            ->groupBy('courses.id', 'courses.title', 'enrollments.status', 'enrollments.enrolled_at')
            ->orderByDesc('enrollments.enrolled_at')
            ->get();

        return response()->json($coursesTimeSpent);
    }

    /**
     * @OA\Get(
     *     path="/learning/student/{studentId}/courses/progress",
     *     summary="Get progress for all enrolled courses",
     *     tags={"Learning"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="studentId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid"),
     *         description="Student ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Courses progress data",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="course_id", type="string", format="uuid"),
     *                 @OA\Property(property="course_title", type="string"),
     *                 @OA\Property(property="enrollment_status", type="string"),
     *                 @OA\Property(property="enrolled_at", type="string", format="date-time"),
     *                 @OA\Property(property="progress", type="integer", description="Progress percentage"),
     *                 @OA\Property(property="total_lessons", type="integer"),
     *                 @OA\Property(property="completed_lessons", type="integer"),
     *                 @OA\Property(property="total_time_spent", type="integer", description="Time spent in seconds")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Student not found")
     * )
     */
    public function getCoursesProgress($studentId)
    {
        $currentUser = Auth::user();

        // Check if user can view this student's data
        if ($currentUser->id !== $studentId && !in_array($currentUser->role, ['ADMIN', 'TEACHER'])) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        // Verify student exists
        $student = \App\Models\User::findOrFail($studentId);

        $coursesProgress = DB::table('enrollments')
            ->join('courses', 'enrollments.course_id', '=', 'courses.id')
            ->leftJoin('modules', 'courses.id', '=', 'modules.course_id')
            ->leftJoin('lessons', 'modules.id', '=', 'lessons.module_id')
            ->leftJoin('progress', function($join) use ($studentId) {
                $join->on('progress.lesson_id', '=', 'lessons.id')
                     ->where('progress.student_id', '=', $studentId);
            })
            ->where('enrollments.student_id', $studentId)
            ->select(
                'courses.id as course_id',
                'courses.title as course_title',
                'enrollments.status as enrollment_status',
                'enrollments.progress',
                'enrollments.enrolled_at',
                DB::raw('COUNT(DISTINCT lessons.id) as total_lessons'),
                DB::raw('COUNT(DISTINCT CASE WHEN progress.is_completed = true THEN progress.lesson_id END) as completed_lessons'),
                DB::raw('COALESCE(SUM(progress.time_spent), 0) as total_time_spent')
            )
            ->groupBy('courses.id', 'courses.title', 'enrollments.status', 'enrollments.progress', 'enrollments.enrolled_at')
            ->orderByDesc('enrollments.enrolled_at')
            ->get();

        return response()->json($coursesProgress);
    }

    /**
     * @OA\Get(
     *     path="/learning/courses/progress",
     *     summary="Get progress for all enrolled courses (current user)",
     *     tags={"Learning"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Courses progress data",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="course_id", type="string", format="uuid"),
     *                 @OA\Property(property="course_title", type="string"),
     *                 @OA\Property(property="enrollment_status", type="string"),
     *                 @OA\Property(property="enrolled_at", type="string", format="date-time"),
     *                 @OA\Property(property="progress", type="integer", description="Progress percentage"),
     *                 @OA\Property(property="total_lessons", type="integer"),
     *                 @OA\Property(property="completed_lessons", type="integer"),
     *                 @OA\Property(property="total_time_spent", type="integer", description="Time spent in seconds")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function getMyCoursesProgress(Request $request)
    {
        $studentId = $request->user()->id;

        $coursesProgress = DB::table('enrollments')
            ->join('courses', 'enrollments.course_id', '=', 'courses.id')
            ->leftJoin('modules', 'courses.id', '=', 'modules.course_id')
            ->leftJoin('lessons', 'modules.id', '=', 'lessons.module_id')
            ->leftJoin('progress', function($join) use ($studentId) {
                $join->on('progress.lesson_id', '=', 'lessons.id')
                     ->where('progress.student_id', '=', $studentId);
            })
            ->where('enrollments.student_id', $studentId)
            ->select(
                'courses.id as course_id',
                'courses.title as course_title',
                'enrollments.status as enrollment_status',
                'enrollments.progress',
                'enrollments.enrolled_at',
                DB::raw('COUNT(DISTINCT lessons.id) as total_lessons'),
                DB::raw('COUNT(DISTINCT CASE WHEN progress.is_completed = true THEN progress.lesson_id END) as completed_lessons'),
                DB::raw('COALESCE(SUM(progress.time_spent), 0) as total_time_spent')
            )
            ->groupBy('courses.id', 'courses.title', 'enrollments.status', 'enrollments.progress', 'enrollments.enrolled_at')
            ->orderByDesc('enrollments.enrolled_at')
            ->get();

        return response()->json($coursesProgress);
    }
}
