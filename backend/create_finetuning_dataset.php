<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = \Illuminate\Http\Request::capture()
);

// Create fine-tuning dataset
$courses = \App\Models\Course::with(['modules.lessons'])->get();

$trainingExamples = [
    // System examples about courses in general
    [
        'text_input' => 'Bạn là ai?',
        'output' => 'Tôi là CertChain AI Assistant, một trợ lý thông minh được hỗ trợ bởi Google Gemini. Tôi được fine-tune trên dữ liệu các khóa học CertChain để giúp học viên tìm hiểu về các khóa học, bài tập và kiến thức chuyên môn.'
    ],
    [
        'text_input' => 'CertChain là gì?',
        'output' => 'CertChain là một nền tảng học trực tuyến tiên tiến sử dụng công nghệ blockchain để cấp và xác minh chứng chỉ kỹ thuật số. Chúng tôi cung cấp các khóa học chất lượng cao về lập trình, web development, quản lý dự án và nhiều lĩnh vực khác.'
    ],
    [
        'text_input' => 'Làm sao để đăng ký khóa học?',
        'output' => 'Bạn có thể duyệt các khóa học có sẵn trên nền tảng, chọn khóa học bạn quan tâm, và nhấn nút "Enroll" hoặc "Đăng ký". Một số khóa học miễn phí, trong khi những khóa học khác yêu cầu thanh toán. Sau khi đăng ký, bạn sẽ có quyền truy cập vào tất cả các bài học và tài liệu.'
    ],
    [
        'text_input' => 'Chứng chỉ có ý nghĩa gì?',
        'output' => 'Chứng chỉ CertChain là một tài liệu kỹ thuật số được xác minh bằng blockchain, chứng tỏ bạn đã hoàn thành một khóa học. Chứng chỉ này có thể được chia sẻ trên LinkedIn, CV, hoặc các nền tảng khác để chứng minh kỹ năng và kiến thức của bạn cho các nhà tuyển dụng.'
    ],
];

// Add examples from actual courses
foreach ($courses as $course) {
    $moduleList = $course->modules->pluck('title')->join(', ');
    $trainingExamples[] = [
        'text_input' => 'Khóa học ' . $course->title . ' là gì?',
        'output' => $course->title . ' là một khóa học mức ' . strtolower($course->level) . '. ' . $course->description . ' Khóa học này bao gồm các module sau: ' . $moduleList . '.'
    ];
    
    // Add module-specific examples
    foreach ($course->modules as $module) {
        $lessonList = $module->lessons->pluck('title')->join(', ');
        if ($lessonList) {
            $trainingExamples[] = [
                'text_input' => 'Module "' . $module->title . '" trong khóa học ' . $course->title . ' là gì?',
                'output' => $module->title . ': ' . $module->description . ' Các bài học trong module này bao gồm: ' . $lessonList . '.'
            ];
        }
    }
}

// Add general teaching philosophy
$trainingExamples[] = [
    'text_input' => 'Làm sao để học hiệu quả trên CertChain?',
    'output' => '1. Xác định mục tiêu học tập của bạn. 2. Theo dõi các bài học theo thứ tự. 3. Làm các bài tập và bài kiểm tra để kiểm tra kiến thức. 4. Tham gia cộng đồng và thảo luận với những học viên khác. 5. Hoàn thành khóa học để nhận chứng chỉ. Hãy dành thời gian hàng ngày cho học tập để đạt kết quả tốt nhất.'
];

// Save to JSONL format for Google's fine-tuning
$jsonlContent = '';
foreach ($trainingExamples as $example) {
    $jsonlContent .= json_encode($example, JSON_UNESCAPED_UNICODE) . "\n";
}

file_put_contents(__DIR__ . '/training_data.jsonl', $jsonlContent);

echo "✅ Fine-tuning dataset created successfully!\n";
echo "📊 Total examples: " . count($trainingExamples) . "\n";
echo "📁 File: training_data.jsonl\n";
echo "\n📝 Sample data:\n";
echo json_encode(array_slice($trainingExamples, 0, 3), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
