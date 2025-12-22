<?php

namespace App\Http\Controllers;

use App\Services\LessonService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenApi\Annotations as OA;

class LessonController extends Controller
{
    protected $lessonService;

    public function __construct(LessonService $lessonService)
    {
        $this->lessonService = $lessonService;
    }

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
            $course = $this->lessonService->getCourseById($courseId);
            
            if (!$course) {
                return response()->json([
                    'success' => false,
                    'message' => 'Course not found'
                ], 404);
            }

            $user = Auth::user();
            if (!$this->lessonService->canManageLesson($courseId, $user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized - only course teacher can add lessons'
                ], 403);
            }

            $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'content' => 'nullable|string',
                'video' => 'nullable|file|mimes:mp4,webm,mov|max:512000',
                'module_id' => 'nullable|exists:modules,id',
                'order' => 'nullable|integer|min:1',
                'duration' => 'nullable|integer|min:1'
            ]);

            $videoFile = $request->hasFile('video') ? $request->file('video') : null;
            $lesson = $this->lessonService->createLesson($courseId, $request->all(), $videoFile);

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
            $course = $this->lessonService->getCourseById($courseId);
            $lesson = $this->lessonService->getLessonById($lessonId);
            
            if (!$course || !$lesson) {
                return response()->json([
                    'success' => false,
                    'message' => 'Course or lesson not found'
                ], 404);
            }

            $user = Auth::user();
            if (!$this->lessonService->canManageLesson($courseId, $user)) {
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

            $videoFile = $request->hasFile('video') ? $request->file('video') : null;
            $updatedLesson = $this->lessonService->updateLesson($lessonId, $request->all(), $videoFile);

            return response()->json([
                'success' => true,
                'data' => $updatedLesson,
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
            $course = $this->lessonService->getCourseById($courseId);
            $lesson = $this->lessonService->getLessonById($lessonId);
            
            if (!$course || !$lesson) {
                return response()->json([
                    'success' => false,
                    'message' => 'Course or lesson not found'
                ], 404);
            }

            $user = Auth::user();
            if (!$this->lessonService->canManageLesson($courseId, $user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $this->lessonService->deleteLesson($lessonId);

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
     *     @OA\Response(response=200, description="Lesson details retrieved successfully"),
     *     @OA\Response(response=404, description="Lesson not found"),
     *     @OA\Response(response=403, description="Access denied - enrollment required")
     * )
     */
    public function show($lessonId)
    {
        try {
            $lesson = $this->lessonService->getLessonById($lessonId);
            
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
     *     @OA\Response(response=200, description="Lessons retrieved successfully"),
     *     @OA\Response(response=404, description="Module not found")
     * )
     */
    public function getByModule($moduleId)
    {
        try {
            $lessons = $this->lessonService->getLessonsByModuleId($moduleId);

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
     *     @OA\Response(response=200, description="Progress retrieved successfully"),
     *     @OA\Response(response=404, description="Lesson not found")
     * )
     */
    public function getProgress($lessonId)
    {
        try {
            $user = Auth::user();
            $progress = $this->lessonService->getLessonProgress($lessonId, $user->id);

            if ($progress === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lesson not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $progress,
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
     *     @OA\Response(response=200, description="Enrollment status retrieved")
     * )
     */
    public function checkEnrollment($courseId)
    {
        try {
            $user = Auth::user();
            $enrollment = $this->lessonService->checkEnrollment($courseId, $user->id);

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

    /**
     * @OA\Get(
     *     path="/lessons/{lessonId}/comments",
     *     summary="Get comments for a lesson",
     *     description="Get all comments for a specific lesson",
     *     tags={"Lesson Comments"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="lessonId",
     *         in="path",
     *         required=true,
     *         description="Lesson ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=200, description="Comments retrieved successfully")
     * )
     */
    public function getComments($lessonId)
    {
        try {
            $comments = $this->lessonService->getComments($lessonId);
            
            if ($comments === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lesson not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'comments' => $comments,
                'message' => 'Comments retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving comments: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/lessons/{lessonId}/comments",
     *     summary="Add a comment to a lesson",
     *     description="Add a new comment to a specific lesson",
     *     tags={"Lesson Comments"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="lessonId",
     *         in="path",
     *         required=true,
     *         description="Lesson ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"content"},
     *             @OA\Property(property="content", type="string"),
     *             @OA\Property(property="parent_id", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Comment added successfully")
     * )
     */
    public function storeComment(Request $request, $lessonId)
    {
        try {
            $request->validate([
                'content' => 'required|string|max:1000',
                'parent_id' => 'nullable|exists:lesson_comments,id'
            ]);

            $user = Auth::user();
            $comment = $this->lessonService->addComment(
                $lessonId,
                $user->id,
                $request->content,
                $request->parent_id
            );
            
            if ($comment === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lesson not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'comment' => $comment,
                'message' => 'Comment added successfully'
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
                'message' => 'Error adding comment: ' . $e->getMessage()
            ], 500);
        }
    }
}
