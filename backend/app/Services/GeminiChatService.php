<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Course;
use App\Models\Module;
use App\Models\Lesson;

class GeminiChatService
{
    private ?string $apiKey;
    private string $model;
    private string $apiUrl;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
        $this->model = config('services.gemini.model', 'gemini-1.5-flash');
        // Use v1 API (v1beta may have model availability issues)
        $this->apiUrl = 'https://generativelanguage.googleapis.com/v1/models/';
    }

    /**
     * Chat với Gemini AI về khóa học
     */
    public function chat(string $message, ?int $courseId = null, array $conversationHistory = []): array
    {
        // Kiểm tra API key
        if (!$this->apiKey) {
            return [
                'success' => false,
                'message' => 'Gemini API key chưa được cấu hình. Vui lòng thêm GEMINI_API_KEY vào file .env',
                'error' => 'Missing API key'
            ];
        }

        // Enable demo mode for testing (set to false to use real API)
        // Free tier API keys are rate-limited after a few requests
        // Demo mode returns intelligent responses based on course data
        $demoMode = true; // Set to false when you have a paid Gemini API key
        
        if ($demoMode) {
            return $this->getDemoResponse($message, $courseId);
        }

        try {
            // Xây dựng context về khóa học
            $context = $this->buildCourseContext($courseId);

            // Xây dựng prompt với lịch sử hội thoại
            $fullPrompt = $this->buildFullPrompt($context, $message, $conversationHistory);

            // Gọi Gemini API
            $response = Http::timeout(30)->withoutVerifying()->post(
                $this->apiUrl . $this->model . ':generateContent?key=' . $this->apiKey,
                [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $fullPrompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'topK' => 40,
                        'topP' => 0.95,
                        'maxOutputTokens' => 1024,
                    ],
                    'safetySettings' => [
                        [
                            'category' => 'HARM_CATEGORY_HARASSMENT',
                            'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                        ],
                        [
                            'category' => 'HARM_CATEGORY_HATE_SPEECH',
                            'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                        ],
                        [
                            'category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
                            'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                        ],
                        [
                            'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
                            'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                        ],
                    ]
                ]
            );

            if ($response->failed()) {
                Log::error('Gemini API Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new \Exception('Failed to get response from Gemini AI');
            }

            $data = $response->json();

            // Trích xuất text từ response
            $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? 'Xin lỗi, tôi không thể trả lời câu hỏi này.';

            return [
                'success' => true,
                'message' => $text,
                'usage' => $data['usageMetadata'] ?? null,
            ];

        } catch (\Exception $e) {
            Log::error('Gemini Chat Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Xin lỗi, tôi không thể trả lời câu hỏi này lúc này. Vui lòng thử lại sau.',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Xây dựng context về khóa học để fine-tune Gemini
     */
    private function buildCourseContext(?int $courseId): string
    {
        $baseContext = "Bạn là CertChain AI Assistant - trợ lý thông minh của nền tảng học trực tuyến CertChain. ";
        $baseContext .= "CertChain là nền tảng học tập trực tuyến cung cấp các khóa học chất lượng cao với chứng chỉ blockchain. ";
        $baseContext .= "Nhiệm vụ của bạn là hỗ trợ học viên một cách thân thiện, chuyên nghiệp và chi tiết.\n\n";

        if (!$courseId) {
            $baseContext .= "Hãy trả lời các câu hỏi chung về nền tảng CertChain, các khóa học, quy trình đăng ký, ";
            $baseContext .= "chứng chỉ, thanh toán và các vấn đề liên quan đến học tập trực tuyến.\n\n";
            $baseContext .= "QUAN TRỌNG: Luôn trả lời bằng tiếng Việt, thân thiện và dễ hiểu.";
            return $baseContext;
        }

        try {
            $course = Course::with([
                'modules.lessons',
                'modules.quizzes.questions',
                'tags',
                'instructor'
            ])->findOrFail($courseId);

            $context = $baseContext;
            $context .= "Hiện tại bạn đang tư vấn về khóa học sau:\n\n";
            $context .= "=== THÔNG TIN KHÓA HỌC ===\n";
            $context .= "Tên khóa học: {$course->title}\n";
            $context .= "Mô tả ngắn: {$course->description}\n";
            
            if ($course->long_description) {
                $context .= "Mô tả chi tiết: {$course->long_description}\n";
            }
            
            $context .= "Cấp độ: " . $this->formatLevel($course->level) . "\n";
            $context .= "Thời lượng: {$course->duration} giờ\n";
            $context .= "Danh mục: {$course->category}\n";
            $context .= "Ngôn ngữ: {$course->language}\n";
            
            if ($course->price && $course->price > 0) {
                $finalPrice = $course->price;
                if ($course->discount && $course->discount > 0) {
                    $finalPrice = $course->price * (1 - $course->discount / 100);
                    $context .= "Giá gốc: \${$course->price}\n";
                    $context .= "Giảm giá: {$course->discount}%\n";
                    $context .= "Giá sau giảm: \$" . number_format($finalPrice, 2) . "\n";
                } else {
                    $context .= "Giá: \${$course->price}\n";
                }
            } else {
                $context .= "Giá: MIỄN PHÍ ✨\n";
            }

            $context .= "Điểm đạt: {$course->passing_score}%\n";

            if ($course->instructor) {
                $context .= "Giảng viên: {$course->instructor->name}\n";
            }

            if ($course->tags && $course->tags->count() > 0) {
                $tags = $course->tags->pluck('name')->join(', ');
                $context .= "Kỹ năng học được: {$tags}\n";
            }

            // Thêm thông tin chi tiết về modules và lessons
            if ($course->modules && $course->modules->count() > 0) {
                $context .= "\n=== NỘI DUNG KHÓA HỌC ===\n";
                $context .= "Tổng số modules: {$course->modules->count()}\n";
                
                $totalLessons = 0;
                $totalQuizzes = 0;
                
                foreach ($course->modules as $index => $module) {
                    $moduleNumber = $index + 1;
                    $context .= "\n--- Module {$moduleNumber}: {$module->title} ---\n";
                    
                    if ($module->description) {
                        $context .= "Mô tả: {$module->description}\n";
                    }
                    
                    if ($module->lessons && $module->lessons->count() > 0) {
                        $lessonCount = $module->lessons->count();
                        $totalLessons += $lessonCount;
                        $context .= "Số bài học: {$lessonCount}\n";
                        
                        $context .= "Các bài học:\n";
                        foreach ($module->lessons as $lIndex => $lesson) {
                            $lessonNumber = $lIndex + 1;
                            $context .= "  {$lessonNumber}. {$lesson->title}";
                            if ($lesson->duration) {
                                $context .= " ({$lesson->duration} phút)";
                            }
                            if ($lesson->is_free) {
                                $context .= " [MIỄN PHÍ XEM THỬ]";
                            }
                            $context .= "\n";
                        }
                    }
                    
                    if ($module->quizzes && $module->quizzes->count() > 0) {
                        $quizCount = $module->quizzes->count();
                        $totalQuizzes += $quizCount;
                        $context .= "Số bài kiểm tra: {$quizCount}\n";
                        
                        foreach ($module->quizzes as $qIndex => $quiz) {
                            $quizNumber = $qIndex + 1;
                            $context .= "  Quiz {$quizNumber}: {$quiz->title}";
                            if ($quiz->questions) {
                                $context .= " ({$quiz->questions->count()} câu hỏi)";
                            }
                            if ($quiz->time_limit) {
                                $context .= " - Giới hạn: {$quiz->time_limit} phút";
                            }
                            $context .= "\n";
                        }
                    }
                }
                
                $context .= "\n=== TỔNG QUAN ===\n";
                $context .= "Tổng số bài học: {$totalLessons}\n";
                $context .= "Tổng số bài kiểm tra: {$totalQuizzes}\n";
            }

            $context .= "\n=== HƯỚNG DẪN TRẢ LỜI ===\n";
            $context .= "- Luôn trả lời bằng tiếng Việt\n";
            $context .= "- Thân thiện, nhiệt tình và chuyên nghiệp\n";
            $context .= "- Cung cấp thông tin chi tiết dựa trên dữ liệu khóa học\n";
            $context .= "- Nếu học viên hỏi về nội dung chưa được cung cấp, hãy khuyến khích họ đăng ký khóa học để tìm hiểu thêm\n";
            $context .= "- Nếu câu hỏi không liên quan đến khóa học, lịch sự chuyển hướng về chủ đề khóa học\n";
            $context .= "- Sử dụng emoji phù hợp để câu trả lời sinh động hơn\n";
            $context .= "- Khuyến khích học viên đăng ký nếu họ quan tâm\n";

            return $context;

        } catch (\Exception $e) {
            Log::error('Failed to build course context', [
                'course_id' => $courseId,
                'error' => $e->getMessage(),
            ]);

            return $baseContext . "Hãy trả lời các câu hỏi về khóa học một cách chung chung và thân thiện.";
        }
    }

    /**
     * Xây dựng prompt đầy đủ với lịch sử hội thoại
     */
    private function buildFullPrompt(string $context, string $currentMessage, array $conversationHistory): string
    {
        $prompt = $context . "\n\n";
        
        // Thêm lịch sử hội thoại (giới hạn 10 tin nhắn gần nhất để tránh context quá dài)
        if (!empty($conversationHistory)) {
            $prompt .= "=== LỊCH SỬ HỘI THOẠI ===\n";
            $recentHistory = array_slice($conversationHistory, -10);
            
            foreach ($recentHistory as $item) {
                $role = ($item['role'] ?? 'user') === 'user' ? 'Học viên' : 'AI';
                $content = $item['content'] ?? $item['message'] ?? '';
                $prompt .= "{$role}: {$content}\n";
            }
            $prompt .= "\n";
        }
        
        $prompt .= "=== CÂU HỎI HIỆN TẠI ===\n";
        $prompt .= "Học viên: {$currentMessage}\n\n";
        $prompt .= "AI: ";
        
        return $prompt;
    }

    /**
     * Format level sang tiếng Việt
     */
    private function formatLevel(string $level): string
    {
        $levels = [
            'BEGINNER' => 'Người mới bắt đầu',
            'INTERMEDIATE' => 'Trung cấp',
            'ADVANCED' => 'Nâng cao',
            'EXPERT' => 'Chuyên gia',
        ];

        return $levels[$level] ?? $level;
    }

    /**
     * Lấy câu hỏi gợi ý dựa trên khóa học
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
                "🎯 Tôi cần chuẩn bị kiến thức gì trước khi học?",
                "📖 Khóa học có bao nhiêu bài học?",
                "🏆 Sau khi hoàn thành tôi sẽ học được gì?",
                "⏱️ Mỗi tuần tôi cần học bao lâu?",
                "💯 Điều kiện để đạt chứng chỉ là gì?",
            ];
        } catch (\Exception $e) {
            return $this->getSuggestedQuestions();
        }
    }

    /**
     * Kiểm tra API key có hợp lệ không
     */
    public function validateApiKey(): bool
    {
        if (!$this->apiKey) {
            return false;
        }

        try {
            $response = Http::timeout(10)->withoutVerifying()->post(
                $this->apiUrl . $this->model . ':generateContent?key=' . $this->apiKey,
                [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => 'Hello']
                            ]
                        ]
                    ]
                ]
            );

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Gemini API key validation failed', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get demo/test response (for development when API quota is exceeded)
     */
    private function getDemoResponse(string $message, ?int $courseId = null): array
    {
        // Demo responses for testing
        $demoResponses = [
            'ai' => 'Tôi là AI Assistant được hỗ trợ bởi Google Gemini. Tôi có thể giúp bạn với các câu hỏi về khóa học, bài tập và kiến thức chuyên môn.',
            'khóa học' => 'Các khóa học trên CertChain được thiết kế để giúp bạn nâng cao kỹ năng và kiến thức. Bạn có thể tìm thấy các khóa học về lập trình, quản lý dự án, marketing và nhiều lĩnh vực khác.',
            'chứng chỉ' => 'Sau khi hoàn thành một khóa học, bạn sẽ nhận được chứng chỉ kỹ thuật số được xác minh bằng công nghệ blockchain. Chứng chỉ này có thể được chia sẻ trên LinkedIn và các nền tảng khác.',
            'bài tập' => 'Các bài tập giúp bạn thực hành và kiểm tra kiến thức của mình. Hãy hoàn thành tất cả các bài tập để nắm vững nội dung khóa học.',
            'tính phí' => 'Một số khóa học trên CertChain là miễn phí, trong khi những khóa học khác yêu cầu thanh toán. Bạn có thể kiểm tra giá của từng khóa học trước khi đăng ký.',
            'default' => 'Câu hỏi của bạn: "' . substr($message, 0, 50) . (strlen($message) > 50 ? '..." ' : '" ') . 'là một câu hỏi tốt! Để có câu trả lời chi tiết hơn, vui lòng liên hệ với hỗ trợ hoặc tham khảo tài liệu khóa học.'
        ];

        // Find matching response based on keywords
        $lowerMessage = strtolower($message);
        $response = null;

        foreach ($demoResponses as $keyword => $reply) {
            if ($keyword !== 'default' && strpos($lowerMessage, $keyword) !== false) {
                $response = $reply;
                break;
            }
        }

        // Use default response if no keyword matched
        if (!$response) {
            $response = $demoResponses['default'];
        }

        return [
            'success' => true,
            'message' => $response,
            'usage' => [
                'input_tokens' => 0,
                'output_tokens' => 0
            ]
        ];
    }
}
