<?php

namespace App\Http\Controllers;

use App\Services\GeminiChatService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AIChatController extends Controller
{
    private GeminiChatService $geminiChatService;

    public function __construct(GeminiChatService $geminiChatService)
    {
        $this->geminiChatService = $geminiChatService;
    }

    /**
     * Chat với Gemini AI
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function chat(Request $request): JsonResponse
    {
        try {
            // Manually parse JSON body to ensure proper UTF-8 handling
            $body = $request->getContent();
            $data = !empty($body) ? json_decode($body, true) : [];
            
            \Illuminate\Support\Facades\Log::info('AI Chat Request', [
                'body_length' => strlen($body),
                'parsed_data' => $data,
            ]);
            
            // Manual validation instead of using request->validate()
            if (empty($data) || !isset($data['message']) || empty($data['message'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu không hợp lệ',
                    'errors' => ['message' => ['The message field is required.']],
                ], 422);
            }

            if (!is_string($data['message']) || strlen($data['message']) > 2000) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu không hợp lệ',
                    'errors' => ['message' => ['The message must be a string with max 2000 characters.']],
                ], 422);
            }

            $validated = [
                'message' => $data['message'],
                'course_id' => isset($data['course_id']) ? (int)$data['course_id'] : null,
                'conversation_history' => $data['conversation_history'] ?? []
            ];

            $result = $this->geminiChatService->chat(
                $validated['message'],
                $validated['course_id'],
                $validated['conversation_history']
            );

            return response()->json($result);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('AI Chat Controller Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Check if it's a quota error
            $statusCode = 500;
            $message = 'Xin lỗi, tôi không thể trả lời câu hỏi này lúc này. Vui lòng thử lại sau.';
            
            if (strpos($e->getMessage(), 'quota') !== false || 
                strpos($e->getMessage(), 'RESOURCE_EXHAUSTED') !== false ||
                strpos($e->getMessage(), '429') !== false) {
                $statusCode = 429;
                $message = 'Dịch vụ AI đạt giới hạn sử dụng. Vui lòng thử lại trong vài giây.';
            }

            return response()->json([
                'success' => false,
                'message' => $message,
                'error' => $e->getMessage(),
            ], $statusCode);
        }
    }

    /**
     * Lấy câu hỏi gợi ý
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function suggestedQuestions(Request $request): JsonResponse
    {
        try {
            $courseId = $request->query('course_id');
            
            $questions = $this->geminiChatService->getSuggestedQuestions(
                $courseId ? (int)$courseId : null
            );

            return response()->json([
                'questions' => $questions
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Suggested Questions Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'questions' => [],
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Kiểm tra trạng thái API
     * 
     * @return JsonResponse
     */
    public function status(): JsonResponse
    {
        try {
            $isValid = $this->geminiChatService->validateApiKey();

            return response()->json([
                'status' => $isValid ? 'active' : 'inactive',
                'message' => $isValid 
                    ? 'Gemini AI is ready' 
                    : 'Gemini API key is invalid or service is unavailable'
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Status Check Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Error checking Gemini API status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
