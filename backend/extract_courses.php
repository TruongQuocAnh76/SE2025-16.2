<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = \Illuminate\Http\Request::capture()
);

// Get all courses with their data
$courses = \App\Models\Course::with(['modules.lessons', 'reviews'])->get();

$data = [];

foreach ($courses as $course) {
    $courseData = [
        'id' => $course->id,
        'title' => $course->title,
        'description' => $course->description,
        'level' => $course->level,
        'category' => $course->category,
        'lessons_count' => $course->modules->sum(fn($m) => $m->lessons->count()),
        'modules' => []
    ];
    
    foreach ($course->modules as $module) {
        $moduleData = [
            'title' => $module->title,
            'description' => $module->description,
            'lessons' => []
        ];
        
        foreach ($module->lessons as $lesson) {
            $moduleData['lessons'][] = [
                'title' => $lesson->title,
                'description' => $lesson->description,
            ];
        }
        
        $courseData['modules'][] = $moduleData;
    }
    
    $data[] = $courseData;
}

echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
