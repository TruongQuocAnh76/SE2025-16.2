<?php

namespace App\LLM\Providers;

use App\LLM\BaseLLMProvider;
use Illuminate\Support\Facades\Http;

/**
 * Google Gemini API Provider
 */
class GeminiProvider extends BaseLLMProvider
{
    protected string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/';

    protected array $availableModels = [
        'gemini-2.0-flash' => 'Gemini 2.0 Flash - Nhanh, đa năng',
        'gemini-2.0-flash-lite' => 'Gemini 2.0 Flash Lite - Nhẹ, tiết kiệm',
        'gemini-1.5-flash' => 'Gemini 1.5 Flash - Ổn định',
        'gemini-1.5-pro' => 'Gemini 1.5 Pro - Mạnh mẽ nhất',
    ];

    public function __construct(?string $apiKey = null, ?string $model = null)
    {
        $apiKey = $apiKey ?? config('services.gemini.api_key');
        $model = $model ?? config('services.gemini.model', 'gemini-2.0-flash');
        parent::__construct($apiKey, $model);
    }

    public function getName(): string
    {
        return 'Gemini';
    }

    protected function getDefaultModel(): string
    {
        return 'gemini-2.0-flash';
    }

    public function getAvailableModels(): array
    {
        return $this->availableModels;
    }

    /**
     * Chat với Gemini API
     */
    public function chat(
        string $message,
        ?string $systemPrompt = null,
        array $conversationHistory = [],
        array $options = []
    ): array {
        if (!$this->isAvailable()) {
            return $this->errorResponse(
                'Gemini API key chưa được cấu hình.',
                'Missing API key'
            );
        }

        $options = $this->mergeOptions($options);

        try {
            $requestBody = $this->buildRequestBody($message, $systemPrompt, $conversationHistory, $options);
            
            $response = Http::timeout($this->timeout)
                ->withoutVerifying()
                ->post(
                    $this->baseUrl . $this->model . ':generateContent?key=' . $this->apiKey,
                    $requestBody
                );

            if ($response->failed()) {
                return $this->handleApiError($response);
            }

            return $this->parseResponse($response->json());

        } catch (\Exception $e) {
            $this->logError('Chat error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->errorResponse(
                'Xin lỗi, không thể kết nối đến AI. Vui lòng thử lại sau.',
                $e->getMessage()
            );
        }
    }

    /**
     * Xây dựng request body cho Gemini API
     */
    protected function buildRequestBody(
        string $message,
        ?string $systemPrompt,
        array $conversationHistory,
        array $options
    ): array {
        $body = [
            'contents' => $this->buildContents($message, $conversationHistory),
            'generationConfig' => [
                'temperature' => $options['temperature'] ?? 0.85,
                'topK' => $options['topK'] ?? 64,
                'topP' => $options['topP'] ?? 0.95,
                'maxOutputTokens' => $options['maxTokens'] ?? 2048,
            ],
            'safetySettings' => $this->getSafetySettings(),
        ];

        // Thêm system instruction nếu có
        if ($systemPrompt) {
            $body['systemInstruction'] = [
                'parts' => [['text' => $systemPrompt]]
            ];
        }

        return $body;
    }

    /**
     * Build contents array với conversation history
     */
    protected function buildContents(string $message, array $conversationHistory): array
    {
        $contents = [];

        // Thêm lịch sử hội thoại
        foreach ($conversationHistory as $item) {
            $role = ($item['role'] ?? 'user') === 'user' ? 'user' : 'model';
            $content = $item['content'] ?? $item['message'] ?? '';
            
            if (!empty($content)) {
                $contents[] = [
                    'role' => $role,
                    'parts' => [['text' => $content]]
                ];
            }
        }

        // Thêm tin nhắn hiện tại
        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $message]]
        ];

        return $contents;
    }

    /**
     * Format conversation history (implement từ base class)
     */
    protected function formatConversationHistory(array $history): array
    {
        return array_map(function ($item) {
            return [
                'role' => ($item['role'] ?? 'user') === 'user' ? 'user' : 'model',
                'parts' => [['text' => $item['content'] ?? $item['message'] ?? '']]
            ];
        }, $history);
    }

    /**
     * Parse response từ Gemini API
     */
    protected function parseResponse(array $response): array
    {
        $text = $response['candidates'][0]['content']['parts'][0]['text'] 
            ?? 'Xin lỗi, tôi không thể trả lời câu hỏi này.';

        $usage = $response['usageMetadata'] ?? null;

        return $this->successResponse($text, $usage);
    }

    /**
     * Xử lý lỗi từ API
     */
    protected function handleApiError($response): array
    {
        $status = $response->status();
        $body = $response->json();
        $errorMessage = $body['error']['message'] ?? 'Unknown error';
        $errorStatus = $body['error']['status'] ?? '';

        $this->logError('API Error', [
            'status' => $status,
            'body' => $response->body(),
        ]);

        // Xử lý các loại lỗi cụ thể
        if ($status === 429 || $errorStatus === 'RESOURCE_EXHAUSTED') {
            return $this->errorResponse(
                'API đã hết quota. Vui lòng thử lại sau hoặc liên hệ admin.',
                'QUOTA_EXCEEDED'
            );
        }

        if ($status === 403) {
            if (str_contains($errorMessage, 'leaked')) {
                return $this->errorResponse(
                    'API key đã bị vô hiệu hóa. Vui lòng liên hệ admin.',
                    'API_KEY_LEAKED'
                );
            }
            return $this->errorResponse(
                'Không có quyền truy cập API.',
                'PERMISSION_DENIED'
            );
        }

        if ($status === 400) {
            return $this->errorResponse(
                'Yêu cầu không hợp lệ. Vui lòng thử lại.',
                'INVALID_REQUEST'
            );
        }

        return $this->errorResponse(
            'Đã xảy ra lỗi khi gọi API. Vui lòng thử lại sau.',
            $errorMessage
        );
    }

    /**
     * Cấu hình safety settings
     */
    protected function getSafetySettings(): array
    {
        return [
            ['category' => 'HARM_CATEGORY_HARASSMENT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
            ['category' => 'HARM_CATEGORY_HATE_SPEECH', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
            ['category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
            ['category' => 'HARM_CATEGORY_DANGEROUS_CONTENT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
        ];
    }

    /**
     * Validate API key
     */
    public function validateApiKey(): bool
    {
        if (!$this->apiKey) {
            return false;
        }

        try {
            $response = Http::timeout(10)
                ->withoutVerifying()
                ->get($this->baseUrl . '?key=' . $this->apiKey);

            return $response->successful();
        } catch (\Exception $e) {
            $this->logError('API key validation failed', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
