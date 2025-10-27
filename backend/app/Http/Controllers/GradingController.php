<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\QuizAttempt;
use App\Services\GradingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Grading",
 *     description="Grading and assessment endpoints"
 * )
 */
class GradingController extends Controller
{
    protected $gradingService;

    public function __construct(GradingService $gradingService)
    {
        $this->gradingService = $gradingService;
    }

    /**
     * @OA\Post(
     *     path="/grading/attempts/{attemptId}/auto-grade",
     *     summary="Auto-grade quiz attempt",
     *     tags={"Grading"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="attemptId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Attempt auto-graded successfully"
     *     )
     * )
     */
    public function autoGradeAttempt(string $attemptId): JsonResponse
    {
        try {
            $attempt = QuizAttempt::findOrFail($attemptId);
            $result = $this->gradingService->autoGradeAttempt($attempt);

            return response()->json([
                'success' => true,
                'data' => $result,
                'message' => 'Attempt auto-graded successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to auto-grade attempt',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/grading/answers/{answerId}/manual-grade",
     *     summary="Manually grade an answer",
     *     tags={"Grading"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="answerId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"points_awarded"},
     *             @OA\Property(property="points_awarded", type="integer", minimum=0),
     *             @OA\Property(property="is_correct", type="boolean", nullable=true),
     *             @OA\Property(property="feedback", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Answer graded successfully"
     *     )
     * )
     */
    public function manualGradeAnswer(Request $request, string $answerId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'points_awarded' => 'required|integer|min:0',
                'is_correct' => 'nullable|boolean',
                'feedback' => 'nullable|string'
            ]);

            $answer = Answer::findOrFail($answerId);
            $result = $this->gradingService->manualGrade(
                $answer,
                $validated['points_awarded'],
                $validated['is_correct'] ?? null
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'graded' => $result,
                    'answer' => $answer->fresh()
                ],
                'message' => 'Answer graded successfully'
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
                'message' => 'Failed to grade answer',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/grading/attempts/{attemptId}/pending",
     *     summary="Get pending answers for manual grading",
     *     tags={"Grading"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="attemptId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Pending answers for grading"
     *     )
     * )
     */
    public function getPendingAnswers(string $attemptId): JsonResponse
    {
        try {
            $attempt = QuizAttempt::findOrFail($attemptId);

            $pendingAnswers = $attempt->answers()
                ->with(['question'])
                ->whereHas('question', function ($query) {
                    $query->whereIn('question_type', ['SHORT_ANSWER', 'ESSAY']);
                })
                ->where('is_correct', null)
                ->get();

            $formattedAnswers = $pendingAnswers->map(function ($answer) {
                return [
                    'id' => $answer->id,
                    'answer_text' => $answer->answer_text,
                    'points_awarded' => $answer->points_awarded,
                    'question' => [
                        'id' => $answer->question->id,
                        'question_text' => $answer->question->question_text,
                        'question_type' => $answer->question->question_type,
                        'points' => $answer->question->points,
                        'correct_answer' => $answer->question->correct_answer,
                        'explanation' => $answer->question->explanation,
                    ]
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedAnswers,
                'meta' => [
                    'total_pending' => $pendingAnswers->count(),
                    'attempt_id' => $attemptId
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve pending answers',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/grading/attempts/{attemptId}/review",
     *     summary="Get attempt results for review",
     *     tags={"Grading"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="attemptId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Attempt review data"
     *     )
     * )
     */
    public function getAttemptReview(string $attemptId): JsonResponse
    {
        try {
            $attempt = QuizAttempt::with(['quiz', 'student', 'answers.question'])->findOrFail($attemptId);

            $answers = $attempt->answers->map(function ($answer) {
                return [
                    'id' => $answer->id,
                    'answer_text' => $answer->answer_text,
                    'is_correct' => $answer->is_correct,
                    'points_awarded' => $answer->points_awarded,
                    'question' => $answer->question->toReviewArray()
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'attempt' => [
                        'id' => $attempt->id,
                        'score' => $attempt->score,
                        'is_passed' => $attempt->is_passed,
                        'time_spent' => $attempt->time_spent,
                        'started_at' => $attempt->started_at,
                        'submitted_at' => $attempt->submitted_at,
                    ],
                    'quiz' => [
                        'id' => $attempt->quiz->id,
                        'title' => $attempt->quiz->title,
                        'passing_score' => $attempt->quiz->passing_score,
                        'total_points' => $attempt->quiz->getTotalPoints(),
                    ],
                    'student' => [
                        'id' => $attempt->student->id,
                        'name' => $attempt->student->name,
                        'email' => $attempt->student->email,
                    ],
                    'answers' => $answers,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve attempt review',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
