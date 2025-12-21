<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\LessonComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenApi\Annotations as OA;

class LessonCommentController extends Controller
{
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
     *     @OA\Response(
     *         response=200,
     *         description="Comments retrieved successfully"
     *     )
     * )
     */
    public function index($lessonId)
    {
        try {
            $lesson = Lesson::find($lessonId);
            
            if (!$lesson) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lesson not found'
                ], 404);
            }

            $comments = LessonComment::with(['user:id,name,avatar_url', 'replies.user:id,name,avatar_url'])
                ->where('lesson_id', $lessonId)
                ->whereNull('parent_id')
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            return response()->json([
                'success' => true,
                'data' => $comments,
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
     *     @OA\Response(
     *         response=201,
     *         description="Comment added successfully"
     *     )
     * )
     */
    public function store(Request $request, $lessonId)
    {
        try {
            $request->validate([
                'content' => 'required|string|max:1000',
                'parent_id' => 'nullable|exists:lesson_comments,id'
            ]);

            $lesson = Lesson::find($lessonId);
            
            if (!$lesson) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lesson not found'
                ], 404);
            }

            $comment = LessonComment::create([
                'lesson_id' => $lessonId,
                'user_id' => Auth::id(),
                'content' => $request->content,
                'parent_id' => $request->parent_id
            ]);

            $comment->load('user:id,name,avatar_url');

            return response()->json([
                'success' => true,
                'data' => $comment,
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

    /**
     * @OA\Put(
     *     path="/comments/{commentId}",
     *     summary="Update a comment",
     *     description="Update an existing comment",
     *     tags={"Lesson Comments"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="commentId",
     *         in="path",
     *         required=true,
     *         description="Comment ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"content"},
     *             @OA\Property(property="content", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Comment updated successfully"
     *     )
     * )
     */
    public function update(Request $request, $commentId)
    {
        try {
            $request->validate([
                'content' => 'required|string|max:1000'
            ]);

            $comment = LessonComment::find($commentId);
            
            if (!$comment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Comment not found'
                ], 404);
            }

            // Only allow the owner to update
            if ($comment->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $comment->update([
                'content' => $request->content
            ]);

            return response()->json([
                'success' => true,
                'data' => $comment,
                'message' => 'Comment updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating comment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/comments/{commentId}",
     *     summary="Delete a comment",
     *     description="Delete an existing comment",
     *     tags={"Lesson Comments"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="commentId",
     *         in="path",
     *         required=true,
     *         description="Comment ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Comment deleted successfully"
     *     )
     * )
     */
    public function destroy($commentId)
    {
        try {
            $comment = LessonComment::find($commentId);
            
            if (!$comment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Comment not found'
                ], 404);
            }

            // Only allow the owner or admin to delete
            $user = Auth::user();
            if ($comment->user_id !== $user->id && $user->role !== 'ADMIN') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $comment->delete();

            return response()->json([
                'success' => true,
                'message' => 'Comment deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting comment: ' . $e->getMessage()
            ], 500);
        }
    }
}
