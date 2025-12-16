<?php

namespace App\LLM;

use App\Contracts\LLMProviderInterface;
use App\LLM\Providers\GeminiProvider;
use App\LLM\Providers\OpenAIProvider;
use InvalidArgumentException;

/**
 * Factory để tạo LLM Provider
 */
class LLMProviderFactory
{
    /**
     * Danh sách các provider đã đăng ký
     */
    protected static array $providers = [
        'gemini' => GeminiProvider::class,
        'openai' => OpenAIProvider::class,
    ];

    /**
     * Tạo provider theo tên
     *
     * @param string $name Tên provider (gemini, openai, claude, etc.)
     * @param array $config Config bổ sung (apiKey, model, etc.)
     * @return LLMProviderInterface
     * @throws InvalidArgumentException
     */
    public static function create(string $name, array $config = []): LLMProviderInterface
    {
        $name = strtolower($name);

        if (!isset(self::$providers[$name])) {
            throw new InvalidArgumentException("LLM Provider '{$name}' không tồn tại. Các provider có sẵn: " . implode(', ', array_keys(self::$providers)));
        }

        $providerClass = self::$providers[$name];
        
        return new $providerClass(
            $config['api_key'] ?? null,
            $config['model'] ?? null
        );
    }

    /**
     * Tạo provider mặc định từ config
     */
    public static function createDefault(): LLMProviderInterface
    {
        $defaultProvider = config('services.llm.default', 'gemini');
        return self::create($defaultProvider);
    }

    /**
     * Đăng ký provider mới
     *
     * @param string $name Tên provider
     * @param string $class Class implement LLMProviderInterface
     */
    public static function register(string $name, string $class): void
    {
        if (!is_subclass_of($class, LLMProviderInterface::class)) {
            throw new InvalidArgumentException("Class {$class} phải implement LLMProviderInterface");
        }

        self::$providers[strtolower($name)] = $class;
    }

    /**
     * Lấy danh sách provider có sẵn
     */
    public static function getAvailableProviders(): array
    {
        return array_keys(self::$providers);
    }

    /**
     * Kiểm tra provider có tồn tại không
     */
    public static function has(string $name): bool
    {
        return isset(self::$providers[strtolower($name)]);
    }

    /**
     * Tạo provider với fallback
     * Nếu provider chính không available, sẽ thử các provider khác
     *
     * @param array $providers Danh sách provider theo thứ tự ưu tiên
     * @return LLMProviderInterface|null
     */
    public static function createWithFallback(array $providers = ['gemini', 'openai']): ?LLMProviderInterface
    {
        foreach ($providers as $providerName) {
            try {
                $provider = self::create($providerName);
                if ($provider->isAvailable()) {
                    return $provider;
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return null;
    }
}
