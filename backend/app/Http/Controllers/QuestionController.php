<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Questions",
 *     description="Question management endpoints"
 * )
 */
class QuestionController extends Controller
{
    /**
     * @OA\Get(
     *     path="/quizzes/{quizId}/questions",
     *     summary="Get questions for a quiz",
     *     tags={"Questions"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="quizId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of questions",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Question"))
     *     )
     * )
     */
    public function index(string $quizId): JsonResponse
    {
        try {
            $quiz = Quiz::findOrFail($quizId);
            $questions = $quiz->questions()->orderBy('order_index')->get();

            $formattedQuestions = $questions->map(function ($question) {
                return $question->toStudentArray();
            });

            return response()->json([
                'success' => true,
                'data' => $formattedQuestions,
                'meta' => [
                    'total' => $questions->count(),
                    'quiz_id' => $quizId,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve questions',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/quizzes/{quizId}/questions",
     *     summary="Create a new question",
     *     tags={"Questions"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="quizId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"question_text", "question_type", "points", "correct_answer"},
     *             @OA\Property(property="question_text", type="string", example="What is Vue.js?"),
     *             @OA\Property(property="question_type", type="string", enum={"MULTIPLE_CHOICE", "TRUE_FALSE", "SHORT_ANSWER", "ESSAY"}),
     *             @OA\Property(property="points", type="integer", example=1),
     *             @OA\Property(property="order_index", type="integer", example=1),
     *             @OA\Property(property="options", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="correct_answer", type="string"),
     *             @OA\Property(property="explanation", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Question created successfully"
     *     )
     * )
     */
    public function store(Request $request, string $quizId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'question_text' => 'required|string',
                'question_type' => 'required|in:MULTIPLE_CHOICE,TRUE_FALSE,SHORT_ANSWER,ESSAY',
                'points' => 'required|integer|min:1',
                'order_index' => 'required|integer|min:1',
                'options' => 'nullable|array',
                'correct_answer' => 'required|string',
                'explanation' => 'nullable|string',
            ]);

            $validated['quiz_id'] = $quizId;
            $question = Question::create($validated);

            return response()->json([
                'success' => true,
                'data' => $question,
                'message' => 'Question created successfully'
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
                'message' => 'Failed to create question',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/questions/{questionId}",
     *     summary="Get question details",
     *     tags={"Questions"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="questionId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Question details"
     *     )
     * )
     */
    public function show(string $questionId): JsonResponse
    {
        try {
            $question = Question::with('quiz')->findOrFail($questionId);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $question->id,
                    'question_text' => $question->question_text,
                    'question_type' => $question->question_type,
                    'points' => $question->points,
                    'order_index' => $question->order_index,
                    'options' => $question->getFormattedOptions(),
                    'explanation' => $question->explanation,
                    'quiz' => [
                        'id' => $question->quiz->id,
                        'title' => $question->quiz->title,
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Question not found'
            ], 404);
        }
    }

    /**
     * @OA\Put(
     *     path="/questions/{questionId}",
     *     summary="Update question",
     *     tags={"Questions"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="questionId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="question_text", type="string"),
     *             @OA\Property(property="question_type", type="string", enum={"MULTIPLE_CHOICE", "TRUE_FALSE", "SHORT_ANSWER", "ESSAY"}),
     *             @OA\Property(property="points", type="integer"),
     *             @OA\Property(property="order_index", type="integer"),
     *             @OA\Property(property="options", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="correct_answer", type="string"),
     *             @OA\Property(property="explanation", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Question updated successfully"
     *     )
     * )
     */
    public function update(Request $request, string $questionId): JsonResponse
    {
        try {
            $question = Question::findOrFail($questionId);

            $validated = $request->validate([
                'question_text' => 'sometimes|string',
                'question_type' => 'sometimes|in:MULTIPLE_CHOICE,TRUE_FALSE,SHORT_ANSWER,ESSAY',
                'points' => 'sometimes|integer|min:1',
                'order_index' => 'sometimes|integer|min:1',
                'options' => 'sometimes|nullable|array',
                'correct_answer' => 'sometimes|string',
                'explanation' => 'sometimes|nullable|string',
            ]);

            $question->update($validated);

            return response()->json([
                'success' => true,
                'data' => $question,
                'message' => 'Question updated successfully'
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
                'message' => 'Failed to update question',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/questions/{questionId}",
     *     summary="Delete question",
     *     tags={"Questions"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="questionId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Question deleted successfully"
     *     )
     * )
     */
    public function destroy(string $questionId): JsonResponse
    {
        try {
            $question = Question::findOrFail($questionId);
            $question->delete();

            return response()->json([
                'success' => true,
                'message' => 'Question deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete question',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
