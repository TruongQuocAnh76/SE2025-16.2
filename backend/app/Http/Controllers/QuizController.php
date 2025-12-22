<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Services\QuizService;
use App\Services\QuizAttemptService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Server(
 *     url="http://localhost:8000/api",
 *     description="Local development server"
 * )
 */
class QuizController extends Controller
{
    protected $quizService;
    protected $attemptService;

    public function __construct(QuizService $quizService, QuizAttemptService $attemptService)
    {
        $this->quizService = $quizService;
        $this->attemptService = $attemptService;
    }

    /**
     * @OA\Get(
     *     path="/courses/{courseId}/quizzes",
     *     summary="Get quizzes for a course",
     *     tags={"Quizzes"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="courseId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of quizzes",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Quiz"))
     *     ),
     *     @OA\Response(response=404, description="Course not found")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $courseId = $request->route('courseId');
            if (!$courseId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Course ID is required'
                ], 400);
            }

            $quizzes = $this->quizService->getQuizzesByCourse($courseId);

            $formattedQuizzes = $quizzes->map(function ($quiz) {
                return [
                    'id' => $quiz->id,
                    'title' => $quiz->title,
                    'description' => $quiz->description,
                    'quiz_type' => $quiz->quiz_type,
                    'time_limit' => $quiz->time_limit,
                    'passing_score' => $quiz->passing_score,
                    'max_attempts' => $quiz->max_attempts,
                    'order_index' => $quiz->order_index,
                    'is_active' => $quiz->is_active,
                    'total_questions' => $quiz->questions->count(),
                    'total_points' => $quiz->getTotalPoints(),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedQuizzes,
                'meta' => [
                    'total' => $formattedQuizzes->count(),
                    'course_id' => $courseId,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve quizzes',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/quizzes/course/{courseId}",
     *     summary="Create a new quiz",
     *     description="Create a quiz for a specific course (teacher only)",
     *     operationId="createQuiz",
     *     tags={"Quizzes"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="courseId",
     *         in="path",
     *         required=true,
     *         description="Course ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "order_index"},
     *             @OA\Property(property="title", type="string", example="Basic Programming Quiz"),
     *             @OA\Property(property="description", type="string", nullable=true, example="Test your programming knowledge"),
     *             @OA\Property(property="quiz_type", type="string", enum={"PRACTICE", "GRADED", "FINAL"}, default="PRACTICE"),
     *             @OA\Property(property="passing_score", type="integer", default=70, example=70),
     *             @OA\Property(property="time_limit", type="integer", nullable=true, example=30),
     *             @OA\Property(property="max_attempts", type="integer", nullable=true, example=3),
     *             @OA\Property(property="order_index", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Quiz created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Quiz")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - not course teacher"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(Request $request, string $courseId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'quiz_type' => 'nullable|in:PRACTICE,GRADED,FINAL',
                'passing_score' => 'nullable|numeric|min:0|max:100',
                'time_limit' => 'nullable|integer|min:1',
                'max_attempts' => 'nullable|integer|min:1',
                'order_index' => 'required|integer',
            ]);

            $validated['course_id'] = $courseId;
            $quiz = $this->quizService->createQuiz($validated);

            return response()->json([
                'success' => true,
                'data' => $quiz,
                'message' => 'Quiz created successfully'
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
                'message' => 'Failed to create quiz',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/quizzes/{quizId}",
     *     summary="Get quiz details",
     *     description="Get detailed information about a specific quiz including questions",
     *     operationId="getQuiz",
     *     tags={"Quizzes"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="quizId",
     *         in="path",
     *         required=true,
     *         description="Quiz ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Quiz details with questions",
     *         @OA\JsonContent(ref="#/components/schemas/Quiz")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Quiz not found"
     *     )
     * )
     */
    public function show(string $quizId): JsonResponse
    {
        try {
            $quiz = Quiz::with(['questions' => function ($query) {
                $query->orderBy('order_index');
            }, 'course'])->find($quizId);

            if (!$quiz) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quiz not found',
                ], 404);
            }

            // Check if user can view this quiz
            $this->authorize('view', $quiz);

            // Format questions for student view (hide correct answers)
            $questions = $quiz->questions->map(function ($question) {
                return $question->toStudentArray();
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $quiz->id,
                    'title' => $quiz->title,
                    'description' => $quiz->description,
                    'quiz_type' => $quiz->quiz_type,
                    'time_limit' => $quiz->time_limit,
                    'passing_score' => $quiz->passing_score,
                    'max_attempts' => $quiz->max_attempts,
                    'max_attempts' => $quiz->max_attempts,
                    'total_points' => $quiz->getTotalPoints(),
                    'total_questions' => $quiz->questions->count(),
                    'course' => $quiz->course ? [
                        'id' => $quiz->course->id,
                        'title' => $quiz->course->title,
                    ] : null,
                    'questions' => $questions,
                ],
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied - enrollment required'
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve quiz',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/quizzes/{quizId}",
     *     summary="Update quiz",
     *     description="Update an existing quiz (teacher only)",
     *     operationId="updateQuiz",
     *     tags={"Quizzes"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="quizId",
     *         in="path",
     *         required=true,
     *         description="Quiz ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="Updated Quiz Title"),
     *             @OA\Property(property="description", type="string", nullable=true),
     *             @OA\Property(property="quiz_type", type="string", enum={"PRACTICE", "GRADED", "FINAL"}),
     *             @OA\Property(property="passing_score", type="integer", example=75),
     *             @OA\Property(property="time_limit", type="integer", nullable=true),
     *             @OA\Property(property="max_attempts", type="integer", nullable=true),
     *             @OA\Property(property="order_index", type="integer"),
     *             @OA\Property(property="is_active", type="boolean")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Quiz updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Quiz")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - not quiz owner"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Quiz not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function update(Request $request, string $quizId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'title' => 'sometimes|string|max:255',
                'description' => 'sometimes|nullable|string',
                'quiz_type' => 'sometimes|in:PRACTICE,GRADED,FINAL',
                'passing_score' => 'sometimes|numeric|min:0|max:100',
                'time_limit' => 'sometimes|nullable|integer|min:1',
                'max_attempts' => 'sometimes|nullable|integer|min:1',
                'order_index' => 'sometimes|integer',
                'is_active' => 'sometimes|boolean',
            ]);

            $quiz = $this->quizService->updateQuiz($quizId, $validated);

            return response()->json([
                'success' => true,
                'data' => $quiz,
                'message' => 'Quiz updated successfully'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update quiz',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/quizzes/{quizId}",
     *     summary="Delete quiz",
     *     description="Delete a quiz (teacher only)",
     *     operationId="deleteQuiz",
     *     tags={"Quizzes"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="quizId",
     *         in="path",
     *         required=true,
     *         description="Quiz ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Quiz deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - not quiz owner"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Quiz not found"
     *     )
     * )
     */
    public function destroy(string $quizId): JsonResponse
    {
        try {
            $this->quizService->deleteQuiz($quizId);

            return response()->json([
                'success' => true,
                'message' => 'Quiz deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete quiz',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/quizzes/{quizId}/start",
     *     summary="Start quiz attempt",
     *     description="Start a new attempt for a quiz",
     *     operationId="startQuizAttempt",
     *     tags={"Quizzes"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="quizId",
     *         in="path",
     *         required=true,
     *         description="Quiz ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Quiz attempt started",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="attempt", ref="#/components/schemas/QuizAttempt"),
     *             @OA\Property(property="questions", type="array", @OA\Items(ref="#/components/schemas/Question"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Maximum attempts reached or not enrolled"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Quiz not found"
     *     )
     * )
     */
    public function startAttempt(string $quizId): JsonResponse
    {
        try {
            $attempt = $this->attemptService->startAttempt(Auth::id(), $quizId);
            $quiz = $this->quizService->getQuizById($quizId);

            // Format questions for student view (hide correct answers)
            $questions = $quiz->questions->map(function ($question) {
                return $question->toStudentArray();
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'attempt' => $attempt,
                    'questions' => $questions,
                    'quiz' => [
                        'title' => $quiz->title,
                        'total_questions' => $quiz->questions->count(),
                        'time_limit' => $quiz->time_limit,
                    ]
                ],
                'message' => 'Quiz attempt started successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to start quiz attempt',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/quizzes/attempt/{attemptId}/submit",
     *     summary="Submit quiz attempt",
     *     description="Submit answers for a quiz attempt",
     *     operationId="submitQuizAttempt",
     *     tags={"Quizzes"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="attemptId",
     *         in="path",
     *         required=true,
     *         description="Attempt ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"answers"},
     *             @OA\Property(property="answers", type="array", @OA\Items(type="string"), description="Array of answers corresponding to questions"),
     *             @OA\Property(property="time_spent", type="integer", nullable=true, description="Time spent in seconds")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Quiz submitted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="score", type="number", format="decimal"),
     *             @OA\Property(property="passed", type="boolean"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Not the attempt owner"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Attempt not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function submitAttempt(Request $request, string $attemptId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'answers' => 'required|array',
                'time_spent' => 'nullable|integer'
            ]);

            $attempt = $this->attemptService->submitAttemptWithDetails($attemptId, $validated['answers']);

            return response()->json([
                'success' => true,
                'data' => [
                    'score' => $attempt->score,
                    'passed' => $attempt->is_passed,
                    'grading_status' => $attempt->grading_status,
                    'message' => $attempt->is_passed ? 'Quiz passed!' : 'Quiz failed'
                ],
                'message' => 'Quiz submitted successfully'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit quiz attempt',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/quizzes/{quizId}/attempts",
     *     summary="Get quiz attempt history",
     *     description="Get all attempts made by the current user for a specific quiz",
     *     operationId="getQuizAttemptsHistory",
     *     tags={"Quizzes"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="quizId",
     *         in="path",
     *         required=true,
     *         description="Quiz ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of quiz attempts",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/QuizAttempt")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Quiz not found"
     *     )
     * )
     */
    public function attemptsHistory(string $quizId): JsonResponse
    {
        try {
            $studentId = Auth::id();
            $attempts = $this->attemptService->getAttemptHistory($studentId, $quizId);

            return response()->json([
                'success' => true,
                'data' => $attempts,
                'meta' => [
                    'total' => $attempts->count(),
                    'quiz_id' => $quizId,
                    'student_id' => $studentId,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve attempt history',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get quiz statistics for a student.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStudentStats(Request $request, string $id): JsonResponse
    {
        try {
            $quiz = Quiz::find($id);

            if (!$quiz) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quiz not found',
                ], 404);
            }

            // TODO: Replace with $request->user()->id after Auth module is done
            $studentId = $request->query('student_id');

            if (!$studentId) {
                return response()->json([
                    'success' => false,
                    'message' => 'student_id is required (temporary - will use auth after Task #4)',
                ], 400);
            }

            $attemptsCount = $quiz->quizAttempts()
                ->where('student_id', $studentId)
                ->count();

            $bestScore = $quiz->quizAttempts()
                ->where('student_id', $studentId)
                ->whereNotNull('score')
                ->max('score');

            $latestAttempt = $quiz->quizAttempts()
                ->where('student_id', $studentId)
                ->latest()
                ->first();

            $hasPassed = $quiz->quizAttempts()
                ->where('student_id', $studentId)
                ->where('is_passed', true)
                ->exists();

            $remainingAttempts = $quiz->max_attempts !== null
                ? max(0, $quiz->max_attempts - $attemptsCount)
                : null;

            return response()->json([
                'success' => true,
                'data' => [
                    'quiz_id' => $quiz->id,
                    'quiz_title' => $quiz->title,
                    'attempts_count' => $attemptsCount,
                    'best_score' => $bestScore,
                    'latest_score' => $latestAttempt->score ?? null,
                    'has_passed' => $hasPassed,
                    'remaining_attempts' => $remainingAttempts,
                    'max_attempts' => $quiz->max_attempts,
                    'can_take_quiz' => $remainingAttempts === null || $remainingAttempts > 0,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve student stats',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get all attempts for a quiz by a student.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStudentAttempts(Request $request, string $id): JsonResponse
    {
        try {
            $quiz = Quiz::find($id);

            if (!$quiz) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quiz not found',
                ], 404);
            }

            // TODO: Replace with $request->user()->id after Auth module is done
            $studentId = $request->query('student_id');

            if (!$studentId) {
                return response()->json([
                    'success' => false,
                    'message' => 'student_id is required (temporary - will use auth after Task #4)',
                ], 400);
            }

            $attempts = $quiz->quizAttempts()
                ->where('student_id', $studentId)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($attempt) {
                    return $attempt->getSummary();
                });

            return response()->json([
                'success' => true,
                'data' => $attempts,
                'meta' => [
                    'total' => $attempts->count(),
                    'quiz_id' => $quiz->id,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve student attempts',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
    /**
     * Get all attempts for a quiz (Teacher only).
     *
     * @param string $quizId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllAttempts(string $quizId): JsonResponse
    {
        try {
            // Check if user is teacher of the course or admin
            $user = Auth::user();
            $quiz = Quiz::with('course')->findOrFail($quizId);
            
            if ($quiz->course->teacher_id !== $user->id && !$user->hasRole('ADMIN')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $attempts = $this->attemptService->getAllAttemptsByQuiz($quizId);

            return response()->json([
                'success' => true,
                'data' => $attempts
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve all attempts',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
