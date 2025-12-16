<?php

namespace App\Http\Controllers;

use App\Services\AIChatService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class AIChatController extends Controller
{
    private AIChatService $aiChatService;

    public function __construct(AIChatService $aiChatService)
    {
        $this->aiChatService = $aiChatService;
    }

    /**
     * Chat với AI
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
            
            Log::info('AI Chat Request', [
                'body_length' => strlen($body),
                'parsed_data' => $data,
            ]);
            
            // Manual validation
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

            $result = $this->aiChatService->chat(
                $data['message'],
                isset($data['course_id']) ? (int)$data['course_id'] : null,
                $data['conversation_history'] ?? []
            );

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('AI Chat Controller Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $statusCode = 500;
            $message = 'Xin lỗi, tôi không thể trả lời câu hỏi này lúc này. Vui lòng thử lại sau.';
            
            if (str_contains($e->getMessage(), 'quota') || 
                str_contains($e->getMessage(), 'RESOURCE_EXHAUSTED') ||
                str_contains($e->getMessage(), '429')) {
                $statusCode = 429;
                $message = 'Dịch vụ AI đạt giới hạn sử dụng. Vui lòng thử lại sau.';
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
     */
    public function suggestedQuestions(Request $request): JsonResponse
    {
        try {
            $courseId = $request->query('course_id');
            
            $questions = $this->aiChatService->getSuggestedQuestions(
                $courseId ? (int)$courseId : null
            );

            return response()->json(['questions' => $questions]);

        } catch (\Exception $e) {
            Log::error('Suggested Questions Error', ['message' => $e->getMessage()]);

            return response()->json([
                'questions' => [],
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Kiểm tra trạng thái AI service
     */
    public function status(): JsonResponse
    {
        try {
            $provider = $this->aiChatService->getProvider();
            $isAvailable = $provider->isAvailable();
            $isValid = $isAvailable && $provider->validateApiKey();

            return response()->json([
                'status' => $isValid ? 'active' : 'inactive',
                'provider' => $provider->getName(),
                'model' => $provider->getModel(),
                'message' => $isValid 
                    ? 'AI service is ready' 
                    : 'AI service is unavailable or API key is invalid',
            ]);

        } catch (\Exception $e) {
            Log::error('Status Check Error', ['message' => $e->getMessage()]);

            return response()->json([
                'status' => 'error',
                'message' => 'Error checking AI service status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Lấy danh sách providers có sẵn
     */
    public function providers(): JsonResponse
    {
        return response()->json([
            'providers' => \App\LLM\LLMProviderFactory::getAvailableProviders(),
            'current' => $this->aiChatService->getProvider()->getName(),
        ]);
    }
}
