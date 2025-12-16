<?php

namespace App\LLM;

use App\Contracts\LLMProviderInterface;
use Illuminate\Support\Facades\Log;

/**
 * Base class cho các LLM Provider
 * Cung cấp các method chung và helper functions
 */
abstract class BaseLLMProvider implements LLMProviderInterface
{
    protected ?string $apiKey;
    protected string $model;
    protected string $baseUrl;
    protected int $timeout = 30;

    /**
     * Default generation config
     */
    protected array $defaultConfig = [
        'temperature' => 0.7,
        'maxTokens' => 2048,
        'topP' => 0.95,
    ];

    public function __construct(?string $apiKey = null, ?string $model = null)
    {
        $this->apiKey = $apiKey;
        $this->model = $model ?? $this->getDefaultModel();
    }

    /**
     * Lấy model mặc định của provider
     */
    abstract protected function getDefaultModel(): string;

    /**
     * Format lịch sử hội thoại theo định dạng của provider
     */
    abstract protected function formatConversationHistory(array $history): array;

    /**
     * Parse response từ API thành format chuẩn
     */
    abstract protected function parseResponse(array $response): array;

    /**
     * Kiểm tra provider có sẵn sàng
     */
    public function isAvailable(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Merge options với default config
     */
    protected function mergeOptions(array $options): array
    {
        return array_merge($this->defaultConfig, $options);
    }

    /**
     * Log error với context
     */
    protected function logError(string $message, array $context = []): void
    {
        Log::error("[{$this->getName()}] {$message}", $context);
    }

    /**
     * Log info
     */
    protected function logInfo(string $message, array $context = []): void
    {
        Log::info("[{$this->getName()}] {$message}", $context);
    }

    /**
     * Tạo response thành công
     */
    protected function successResponse(string $message, ?array $usage = null): array
    {
        return [
            'success' => true,
            'message' => $message,
            'usage' => $usage,
            'provider' => $this->getName(),
            'model' => $this->model,
        ];
    }

    /**
     * Tạo response lỗi
     */
    protected function errorResponse(string $message, ?string $error = null): array
    {
        return [
            'success' => false,
            'message' => $message,
            'error' => $error,
            'provider' => $this->getName(),
            'model' => $this->model,
        ];
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function setModel(string $model): self
    {
        $this->model = $model;
        return $this;
    }

    public function setTimeout(int $seconds): self
    {
        $this->timeout = $seconds;
        return $this;
    }
}
