<?php

namespace App\Services;

use App\Contracts\LLMProviderInterface;
use App\LLM\LLMProviderFactory;
use App\Models\Course;
use Illuminate\Support\Facades\Log;

/**
 * Service xử lý chat AI cho CertChain
 * Sử dụng LLMProviderInterface để tương tác với các LLM provider khác nhau
 */
class AIChatService
{
    private LLMProviderInterface $provider;
    private bool $demoMode;

    public function __construct(?LLMProviderInterface $provider = null)
    {
        $this->provider = $provider ?? LLMProviderFactory::createDefault();
        $this->demoMode = config('services.gemini.demo_mode', false);
    }

    /**
     * Chat với AI về khóa học
     */
    public function chat(string $message, ?int $courseId = null, array $conversationHistory = []): array
    {
        // Demo mode cho development/testing
        if ($this->demoMode) {
            return $this->getDemoResponse($message, $courseId);
        }

        // Kiểm tra provider có sẵn sàng không
        if (!$this->provider->isAvailable()) {
            return [
                'success' => false,
                'message' => 'AI service chưa được cấu hình. Vui lòng liên hệ admin.',
                'error' => 'Provider not available'
            ];
        }

        // Xây dựng context và system prompt
        $context = $this->buildCourseContext($courseId);
        $systemPrompt = $this->getSystemInstruction() . "\n\n" . $context;

        // Gọi LLM provider
        $result = $this->provider->chat(
            $message,
            $systemPrompt,
            $conversationHistory,
            [
                'temperature' => 0.85,
                'maxTokens' => 2048,
            ]
        );

        // Log để debug
        Log::info('AI Chat Response', [
            'provider' => $this->provider->getName(),
            'model' => $this->provider->getModel(),
            'success' => $result['success'],
            'course_id' => $courseId,
        ]);

        return $result;
    }

    /**
     * Đổi LLM provider
     */
    public function setProvider(LLMProviderInterface $provider): self
    {
        $this->provider = $provider;
        return $this;
    }

    /**
     * Lấy provider hiện tại
     */
    public function getProvider(): LLMProviderInterface
    {
        return $this->provider;
    }

    /**
     * Bật/tắt demo mode
     */
    public function setDemoMode(bool $enabled): self
    {
        $this->demoMode = $enabled;
        return $this;
    }

    /**
     * Xây dựng context về khóa học
     */
    private function buildCourseContext(?int $courseId): string
    {
        if (!$courseId) {
            return $this->getGeneralContext();
        }

        try {
            $course = Course::with([
                'modules.lessons',
                'modules.quizzes.questions',
                'tags',
                'instructor'
            ])->findOrFail($courseId);

            return $this->formatCourseContext($course);

        } catch (\Exception $e) {
            Log::error('Failed to build course context', [
                'course_id' => $courseId,
                'error' => $e->getMessage(),
            ]);

            return "Không tìm thấy thông tin khóa học.";
        }
    }

    /**
     * Format thông tin khóa học thành context
     */
    private function formatCourseContext($course): string
    {
        $context = "📚 **{$course->title}**\n";
        $context .= "Mô tả: {$course->description}\n";
        
        if ($course->long_description) {
            $context .= "Chi tiết: {$course->long_description}\n";
        }
        
        $context .= "Cấp độ: " . $this->formatLevel($course->level) . "\n";
        $context .= "Thời lượng: {$course->duration} giờ\n";
        $context .= "Danh mục: {$course->category}\n";
        
        // Giá
        if ($course->price && $course->price > 0) {
            $finalPrice = $course->price;
            if ($course->discount && $course->discount > 0) {
                $finalPrice = $course->price * (1 - $course->discount / 100);
                $context .= "Giá: \${$course->price} → \$" . number_format($finalPrice, 2) . " (-{$course->discount}%)\n";
            } else {
                $context .= "Giá: \${$course->price}\n";
            }
        } else {
            $context .= "Giá: MIỄN PHÍ ✨\n";
        }

        // Giảng viên
        if ($course->instructor) {
            $context .= "Giảng viên: {$course->instructor->name}\n";
        }

        // Tags
        if ($course->tags && $course->tags->count() > 0) {
            $context .= "Kỹ năng: " . $course->tags->pluck('name')->join(', ') . "\n";
        }

        // Modules
        if ($course->modules && $course->modules->count() > 0) {
            $context .= "\nNội dung: {$course->modules->count()} modules\n";
            
            foreach ($course->modules as $i => $module) {
                $lessonCount = $module->lessons ? $module->lessons->count() : 0;
                $context .= "- Module " . ($i + 1) . ": {$module->title} ({$lessonCount} bài)\n";
            }
        }

        return $context;
    }

    /**
     * Context chung khi không có khóa học
     */
    private function getGeneralContext(): string
    {
        $courses = Course::take(5)->get(['id', 'title', 'category', 'price']);
        
        $context = "Người dùng đang ở trang chung.\n\n";
        
        if ($courses->count() > 0) {
            $context .= "Khóa học nổi bật:\n";
            foreach ($courses as $course) {
                $price = $course->price > 0 ? "\${$course->price}" : "Miễn phí";
                $context .= "- {$course->title} ({$course->category}) - {$price}\n";
            }
        }
        
        return $context;
    }

    /**
     * System instruction cho AI
     */
    private function getSystemInstruction(): string
    {
        return <<<PROMPT
Bạn là "Cert" - trợ lý AI thân thiện của CertChain, nền tảng học trực tuyến với chứng chỉ blockchain.

## TÍNH CÁCH:
- Thân thiện, nhiệt tình như người bạn đồng hành học tập
- Nói chuyện tự nhiên, hay dùng emoji 😊
- Luôn khuyến khích và động viên người học

## CÁCH TRẢ LỜI:
- Ngắn gọn, đi thẳng vào vấn đề (3-4 câu cho câu hỏi đơn giản)
- Dùng bullet points khi liệt kê
- Tránh lặp lại thông tin

## VỀ CERTCHAIN:
- 📚 Khóa học đa dạng: lập trình, AI, blockchain, marketing...
- 🎓 Chứng chỉ blockchain: xác thực vĩnh viễn, không thể làm giả
- 💰 Có cả khóa miễn phí và trả phí
- 👨‍🏫 Giảng viên chất lượng

## NGUYÊN TẮC:
- LUÔN trả lời bằng tiếng Việt
- Nếu không biết, nói thật: "Mình chưa có thông tin về vấn đề này"
- Khuyến khích đăng ký học nhưng không spam
PROMPT;
    }

    /**
     * Format level
     */
    private function formatLevel(string $level): string
    {
        return match ($level) {
            'BEGINNER' => 'Người mới',
            'INTERMEDIATE' => 'Trung cấp',
            'ADVANCED' => 'Nâng cao',
            'EXPERT' => 'Chuyên gia',
            default => $level,
        };
    }

    /**
     * Lấy câu hỏi gợi ý
     */
    public function getSuggestedQuestions(?int $courseId = null): array
    {
        if (!$courseId) {
            return [
                '🎓 CertChain có những khóa học gì?',
                '📜 Làm thế nào để nhận chứng chỉ?',
                '💰 Tôi có thể thanh toán bằng cách nào?',
                '⏰ Tôi có thể học bất cứ lúc nào không?',
                '🎯 Làm sao để theo dõi tiến độ học tập?',
            ];
        }

        try {
            $course = Course::findOrFail($courseId);
            return [
                "📚 Khóa học {$course->title} phù hợp với ai?",
                "🎯 Tôi cần chuẩn bị gì trước khi học?",
                "📖 Khóa học có bao nhiêu bài học?",
                "🏆 Sau khi hoàn thành tôi sẽ học được gì?",
                "💯 Điều kiện để đạt chứng chỉ là gì?",
            ];
        } catch (\Exception $e) {
            return $this->getSuggestedQuestions();
        }
    }

    /**
     * Demo response cho testing
     */
    private function getDemoResponse(string $message, ?int $courseId = null): array
    {
        $responses = [
            'xin chào' => 'Chào bạn! 👋 Mình là Cert, trợ lý AI của CertChain. Mình có thể giúp gì cho bạn hôm nay?',
            'khóa học' => 'CertChain có nhiều khóa học thú vị lắm! 📚 Từ lập trình, AI, blockchain đến marketing và soft skills. Bạn quan tâm lĩnh vực nào?',
            'chứng chỉ' => 'Chứng chỉ của CertChain được lưu trên blockchain nên không thể làm giả! 🎓 Hoàn thành khóa học và đạt điểm yêu cầu là bạn sẽ nhận được ngay.',
            'giá' => 'CertChain có cả khóa miễn phí và trả phí. 💰 Bạn có thể bắt đầu với các khóa miễn phí trước nhé!',
            'ai' => 'Mình là Cert - trợ lý AI được tạo ra để hỗ trợ bạn học tập trên CertChain! 🤖 Hỏi mình bất cứ điều gì về khóa học nhé.',
        ];

        $lowerMessage = mb_strtolower($message);
        
        foreach ($responses as $keyword => $reply) {
            if (str_contains($lowerMessage, $keyword)) {
                return [
                    'success' => true,
                    'message' => $reply,
                    'provider' => 'demo',
                    'model' => 'demo-mode',
                ];
            }
        }

        return [
            'success' => true,
            'message' => 'Câu hỏi hay đó! 🤔 Để mình tìm hiểu thêm. Bạn có thể hỏi cụ thể hơn về khóa học, chứng chỉ, hoặc cách học trên CertChain nhé!',
            'provider' => 'demo',
            'model' => 'demo-mode',
        ];
    }
}
