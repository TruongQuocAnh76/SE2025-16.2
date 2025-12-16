<?php

namespace App\LLM\Providers;

use App\LLM\BaseLLMProvider;
use Illuminate\Support\Facades\Http;

/**
 * OpenAI API Provider (GPT-4, GPT-3.5, etc.)
 */
class OpenAIProvider extends BaseLLMProvider
{
    protected string $baseUrl = 'https://api.openai.com/v1/';

    protected array $availableModels = [
        'gpt-4o' => 'GPT-4o - Mạnh mẽ nhất, đa phương thức',
        'gpt-4o-mini' => 'GPT-4o Mini - Nhanh, tiết kiệm',
        'gpt-4-turbo' => 'GPT-4 Turbo - Cân bằng',
        'gpt-3.5-turbo' => 'GPT-3.5 Turbo - Tiết kiệm nhất',
    ];

    public function __construct(?string $apiKey = null, ?string $model = null)
    {
        $apiKey = $apiKey ?? config('services.openai.api_key');
        $model = $model ?? config('services.openai.model', 'gpt-4o-mini');
        parent::__construct($apiKey, $model);
    }

    public function getName(): string
    {
        return 'OpenAI';
    }

    protected function getDefaultModel(): string
    {
        return 'gpt-4o-mini';
    }

    public function getAvailableModels(): array
    {
        return $this->availableModels;
    }

    /**
     * Chat với OpenAI API
     */
    public function chat(
        string $message,
        ?string $systemPrompt = null,
        array $conversationHistory = [],
        array $options = []
    ): array {
        if (!$this->isAvailable()) {
            return $this->errorResponse(
                'OpenAI API key chưa được cấu hình.',
                'Missing API key'
            );
        }

        $options = $this->mergeOptions($options);

        try {
            $messages = $this->buildMessages($message, $systemPrompt, $conversationHistory);
            
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($this->baseUrl . 'chat/completions', [
                    'model' => $this->model,
                    'messages' => $messages,
                    'temperature' => $options['temperature'] ?? 0.7,
                    'max_tokens' => $options['maxTokens'] ?? 2048,
                    'top_p' => $options['topP'] ?? 0.95,
                ]);

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
                'Xin lỗi, không thể kết nối đến OpenAI. Vui lòng thử lại sau.',
                $e->getMessage()
            );
        }
    }

    /**
     * Build messages array cho OpenAI
     */
    protected function buildMessages(string $message, ?string $systemPrompt, array $conversationHistory): array
    {
        $messages = [];

        // System prompt
        if ($systemPrompt) {
            $messages[] = [
                'role' => 'system',
                'content' => $systemPrompt
            ];
        }

        // Conversation history
        foreach ($conversationHistory as $item) {
            $role = ($item['role'] ?? 'user') === 'user' ? 'user' : 'assistant';
            $content = $item['content'] ?? $item['message'] ?? '';
            
            if (!empty($content)) {
                $messages[] = [
                    'role' => $role,
                    'content' => $content
                ];
            }
        }

        // Current message
        $messages[] = [
            'role' => 'user',
            'content' => $message
        ];

        return $messages;
    }

    protected function formatConversationHistory(array $history): array
    {
        return array_map(function ($item) {
            return [
                'role' => ($item['role'] ?? 'user') === 'user' ? 'user' : 'assistant',
                'content' => $item['content'] ?? $item['message'] ?? ''
            ];
        }, $history);
    }

    protected function parseResponse(array $response): array
    {
        $text = $response['choices'][0]['message']['content'] 
            ?? 'Xin lỗi, tôi không thể trả lời câu hỏi này.';

        $usage = $response['usage'] ?? null;

        return $this->successResponse($text, $usage);
    }

    protected function handleApiError($response): array
    {
        $status = $response->status();
        $body = $response->json();
        $errorMessage = $body['error']['message'] ?? 'Unknown error';

        $this->logError('API Error', [
            'status' => $status,
            'body' => $response->body(),
        ]);

        if ($status === 429) {
            return $this->errorResponse(
                'API đã hết quota hoặc rate limit. Vui lòng thử lại sau.',
                'RATE_LIMITED'
            );
        }

        if ($status === 401) {
            return $this->errorResponse(
                'API key không hợp lệ.',
                'INVALID_API_KEY'
            );
        }

        return $this->errorResponse(
            'Đã xảy ra lỗi khi gọi OpenAI API.',
            $errorMessage
        );
    }

    public function validateApiKey(): bool
    {
        if (!$this->apiKey) {
            return false;
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                ])
                ->get($this->baseUrl . 'models');

            return $response->successful();
        } catch (\Exception $e) {
            $this->logError('API key validation failed', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
