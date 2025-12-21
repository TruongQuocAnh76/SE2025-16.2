<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\Course;
use App\Models\Module;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use OpenApi\Annotations as OA;

class LessonController extends Controller
{
    /**
     * @OA\Post(
     *     path="/courses/{courseId}/lessons",
     *     summary="Create a new lesson",
     *     description="Create a new lesson for a course (teacher only)",
     *     tags={"Lessons"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="courseId",
     *         in="path",
     *         required=true,
     *         description="Course ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title"},
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="content", type="string"),
     *             @OA\Property(property="video", type="string", format="binary"),
     *             @OA\Property(property="module_id", type="string"),
     *             @OA\Property(property="order", type="integer"),
     *             @OA\Property(property="duration", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Lesson created successfully"),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     */
    public function store(Request $request, $courseId)
    {
        try {
            $course = Course::find($courseId);
            
            if (!$course) {
                return response()->json([
                    'success' => false,
                    'message' => 'Course not found'
                ], 404);
            }

            // Check if user is the teacher of this course
            $user = Auth::user();
            if ($course->teacher_id !== $user->id && $user->role !== 'ADMIN') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized - only course teacher can add lessons'
                ], 403);
            }

            $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'content' => 'nullable|string',
                'video' => 'nullable|file|mimes:mp4,webm,mov|max:512000', // 500MB max
                'module_id' => 'nullable|exists:modules,id',
                'order' => 'nullable|integer|min:1',
                'duration' => 'nullable|integer|min:1'
            ]);

            // Get or create default module
            $moduleId = $request->module_id;
            if (!$moduleId) {
                $defaultModule = Module::firstOrCreate(
                    ['course_id' => $courseId, 'title' => 'Main Content'],
                    ['order_index' => 1]
                );
                $moduleId = $defaultModule->id;
            }

            // Handle video upload
            $videoUrl = null;
            if ($request->hasFile('video')) {
                $videoPath = $request->file('video')->store('lessons/videos', 'public');
                $videoUrl = Storage::url($videoPath);
            }

            // Get next order index
            $orderIndex = $request->order ?? (Lesson::where('module_id', $moduleId)->max('order_index') + 1);

            $lesson = Lesson::create([
                'module_id' => $moduleId,
                'title' => $request->title,
                'description' => $request->description,
                'content' => $request->content,
                'video_url' => $videoUrl,
                'order_index' => $orderIndex,
                'duration' => $request->duration
            ]);

            return response()->json([
                'success' => true,
                'data' => $lesson,
                'message' => 'Lesson created successfully'
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating lesson: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/courses/{courseId}/lessons/{lessonId}",
     *     summary="Update a lesson",
     *     description="Update an existing lesson (teacher only)",
     *     tags={"Lessons"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Lesson updated successfully"),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     */
    public function update(Request $request, $courseId, $lessonId)
    {
        try {
            $course = Course::find($courseId);
            $lesson = Lesson::find($lessonId);
            
            if (!$course || !$lesson) {
                return response()->json([
                    'success' => false,
                    'message' => 'Course or lesson not found'
                ], 404);
            }

            // Check if user is the teacher of this course
            $user = Auth::user();
            if ($course->teacher_id !== $user->id && $user->role !== 'ADMIN') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $request->validate([
                'title' => 'sometimes|string|max:255',
                'description' => 'nullable|string',
                'content' => 'nullable|string',
                'video' => 'nullable|file|mimes:mp4,webm,mov|max:512000',
                'module_id' => 'nullable|exists:modules,id',
                'order' => 'nullable|integer|min:1',
                'duration' => 'nullable|integer|min:1'
            ]);

            // Handle video upload
            if ($request->hasFile('video')) {
                // Delete old video if exists
                if ($lesson->video_url) {
                    $oldPath = str_replace('/storage/', '', $lesson->video_url);
                    Storage::disk('public')->delete($oldPath);
                }
                $videoPath = $request->file('video')->store('lessons/videos', 'public');
                $lesson->video_url = Storage::url($videoPath);
            }

            $lesson->update(array_filter([
                'title' => $request->title,
                'description' => $request->description,
                'content' => $request->content,
                'module_id' => $request->module_id,
                'order_index' => $request->order,
                'duration' => $request->duration
            ]));

            return response()->json([
                'success' => true,
                'data' => $lesson->fresh(),
                'message' => 'Lesson updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating lesson: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/courses/{courseId}/lessons/{lessonId}",
     *     summary="Delete a lesson",
     *     description="Delete an existing lesson (teacher only)",
     *     tags={"Lessons"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Lesson deleted successfully"),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     */
    public function destroy($courseId, $lessonId)
    {
        try {
            $course = Course::find($courseId);
            $lesson = Lesson::find($lessonId);
            
            if (!$course || !$lesson) {
                return response()->json([
                    'success' => false,
                    'message' => 'Course or lesson not found'
                ], 404);
            }

            // Check if user is the teacher of this course
            $user = Auth::user();
            if ($course->teacher_id !== $user->id && $user->role !== 'ADMIN') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            // Delete video file if exists
            if ($lesson->video_url) {
                $path = str_replace('/storage/', '', $lesson->video_url);
                Storage::disk('public')->delete($path);
            }

            $lesson->delete();

            return response()->json([
                'success' => true,
                'message' => 'Lesson deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting lesson: ' . $e->getMessage()
            ], 500);
        }
    }

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