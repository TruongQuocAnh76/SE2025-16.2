<?php

namespace App\Contracts;

/**
 * Interface cho các LLM Provider (Gemini, OpenAI, Claude, etc.)
 */
interface LLMProviderInterface
{
    /**
     * Gửi tin nhắn và nhận phản hồi từ LLM
     *
     * @param string $message Tin nhắn của người dùng
     * @param string|null $systemPrompt System instruction/prompt
     * @param array $conversationHistory Lịch sử hội thoại
     * @param array $options Các tùy chọn bổ sung (temperature, maxTokens, etc.)
     * @return array ['success' => bool, 'message' => string, 'usage' => array|null, 'error' => string|null]
     */
    public function chat(
        string $message,
        ?string $systemPrompt = null,
        array $conversationHistory = [],
        array $options = []
    ): array;

    /**
     * Kiểm tra API key có hợp lệ không
     *
     * @return bool
     */
    public function validateApiKey(): bool;

    /**
     * Lấy tên của provider
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Lấy model đang sử dụng
     *
     * @return string
     */
    public function getModel(): string;

    /**
     * Kiểm tra provider có sẵn sàng sử dụng không
     *
     * @return bool
     */
    public function isAvailable(): bool;

    /**
     * Lấy danh sách models có sẵn
     *
     * @return array
     */
    public function getAvailableModels(): array;
}
