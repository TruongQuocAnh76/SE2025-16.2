<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenApi\Annotations as OA;

class LessonController extends Controller
{
    /**
     * @OA\Get(
     *     path="/lessons/{lessonId}",
     *     summary="Get lesson details",
     *     description="Get detailed information about a specific lesson",
     *     tags={"Lessons"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="lessonId",
     *         in="path",
     *         required=true,
     *         description="Lesson ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lesson details retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string", example="Lesson retrieved successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Lesson not found"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Access denied - enrollment required"
     *     )
     * )
     */
    public function show($lessonId)
    {
        try {
            $lesson = Lesson::with(['module.course'])->find($lessonId);
            
            if (!$lesson) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lesson not found'
                ], 404);
            }

            // Use policy to check if user can view this lesson
            $this->authorize('view', $lesson);

            return response()->json([
                'success' => true,
                'data' => $lesson,
                'message' => 'Lesson retrieved successfully'
            ]);

        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied - enrollment required'
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving lesson: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/modules/{moduleId}/lessons",
     *     summary="Get lessons by module",
     *     description="Get all lessons in a specific module",
     *     tags={"Lessons"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="moduleId",
     *         in="path",
     *         required=true,
     *         description="Module ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lessons retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="message", type="string", example="Lessons retrieved successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Module not found"
     *     )
     * )
     */
    public function getByModule($moduleId)
    {
        try {
            $lessons = Lesson::where('module_id', $moduleId)
                ->orderBy('order_index')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $lessons,
                'message' => 'Lessons retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving lessons: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/learning/lesson/{lessonId}/progress",
     *     summary="Get lesson progress",
     *     description="Get progress information for a specific lesson",
     *     tags={"Learning Progress"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="lessonId",
     *         in="path",
     *         required=true,
     *         description="Lesson ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Progress retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string", example="Progress retrieved successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Lesson not found"
     *     )
     * )
     */
    public function getProgress($lessonId)
    {
        try {
            $user = Auth::user();
            
            $lesson = Lesson::find($lessonId);
            if (!$lesson) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lesson not found'
                ], 404);
            }

            $progress = \App\Models\Progress::where('student_id', $user->id)
                ->where('lesson_id', $lessonId)
                ->first();

            return response()->json([
                'success' => true,
                'data' => $progress ?: [
                    'lesson_id' => $lessonId,
                    'student_id' => $user->id,
                    'is_completed' => false,
                    'time_spent' => 0,
                    'completed_at' => null
                ],
                'message' => 'Progress retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving progress: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/courses/{courseId}/enrollment/check",
     *     summary="Check course enrollment",
     *     description="Check if user is enrolled in a course",
     *     tags={"Courses"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="courseId",
     *         in="path",
     *         required=true,
     *         description="Course ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Enrollment status retrieved",
     *         @OA\JsonContent(
     *             @OA\Property(property="enrolled", type="boolean"),
     *             @OA\Property(property="enrollment_date", type="string", format="date-time", nullable=true)
     *         )
     *     )
     * )
     */
    public function checkEnrollment($courseId)
    {
        try {
            $user = Auth::user();
            
            $enrollment = Enrollment::where('student_id', $user->id)
                ->where('course_id', $courseId)
                ->first();

            return response()->json([
                'enrolled' => $enrollment ? true : false,
                'enrollment_date' => $enrollment ? $enrollment->created_at : null
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'enrolled' => false,
                'enrollment_date' => null
            ]);
        }
    }
}