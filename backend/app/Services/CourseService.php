<?php namespace App\Services;
use App\Repositories\CourseRepository;
use App\Repositories\EnrollmentRepository;
use App\Repositories\ModuleRepository;
use App\Repositories\LessonRepository;
use App\Repositories\QuizRepository;
use App\Repositories\QuestionRepository;
use App\Models\Review;
use App\Services\HlsVideoService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class CourseService {
    protected $courseRepository;
    protected $enrollmentRepository;
    protected $moduleRepository;
    protected $lessonRepository;
    protected $quizRepository;
    protected $questionRepository;
    protected $hlsVideoService;

    public function __construct(
        CourseRepository $courseRepository,
        EnrollmentRepository $enrollmentRepository,
        ModuleRepository $moduleRepository,
        LessonRepository $lessonRepository,
        QuizRepository $quizRepository,
        QuestionRepository $questionRepository,
        HlsVideoService $hlsVideoService
    ) {
        $this->courseRepository = $courseRepository;
        $this->enrollmentRepository = $enrollmentRepository;
        $this->moduleRepository = $moduleRepository;
        $this->lessonRepository = $lessonRepository;
        $this->quizRepository = $quizRepository;
        $this->questionRepository = $questionRepository;
        $this->hlsVideoService = $hlsVideoService;
    }
    public function getAllCourses($filters = [], $perPage = 12) {
        return $this->courseRepository->getAll($filters, $perPage);
    }
    public function getCourseById($id) {
        return $this->courseRepository->getById($id);
    }
    public function createCourse(array $data) {
        $data['id'] = Str::uuid();
        $data['slug'] = Str::slug($data['title']) . '-' . Str::random(5);
        $data['status'] = 'DRAFT';
        return $this->courseRepository->create($data);
    }

    public function createCourseWithModules(array $data) {
        \DB::beginTransaction();

        try {
            // Extract modules data
            $modules = $data['modules'] ?? [];
            $tagIds = $data['tags'] ?? [];

            // Remove modules and tags from course data
            unset($data['modules'], $data['tags']);

            // Create course
            $data['id'] = Str::uuid();
            $data['slug'] = Str::slug($data['title']) . '-' . Str::random(5);
            $data['status'] = 'DRAFT';

            // Generate thumbnail URL if not provided
            $thumbnailUploadUrl = null;
            if (empty($data['thumbnail'])) {
                $thumbnailPath = 'courses/thumbnails/' . $data['id'] . '.jpg';
                $awsEndpoint = env('AWS_ENDPOINT');
                $awsBucket = env('AWS_BUCKET');
                $data['thumbnail'] = $awsEndpoint . '/' . $awsBucket . '/' . $thumbnailPath;
                $thumbnailUploadUrl = Storage::disk('s3')->temporaryUrl(
                    $thumbnailPath,
                    now()->addMinutes(30),
                    ['ContentType' => 'image/jpeg']
                );
            }

            $course = $this->courseRepository->create($data);

            // Attach tags
            if (!empty($tagIds)) {
                $course->tags()->attach($tagIds);
            }

            // Create modules with lessons and quizzes
            $videoUploadUrls = [];
            if (!empty($modules)) {
                foreach ($modules as $moduleIndex => $moduleData) {
                    $lessons = $moduleData['lessons'] ?? [];
                    $quizzes = $moduleData['quizzes'] ?? [];

                    unset($moduleData['lessons'], $moduleData['quizzes']);

                    $moduleData['id'] = Str::uuid();
                    $moduleData['course_id'] = $course->id;

                    $module = $this->moduleRepository->create($moduleData);

                    // Create lessons with video upload URLs
                    foreach ($lessons as $lessonIndex => $lessonData) {
                        $lessonData['id'] = Str::uuid();
                        $lessonData['module_id'] = $module->id;
                        
                        // Generate HLS video storage paths and upload URL for original video
                        if (empty($lessonData['content_url'])) {
                            $originalVideoPath = 'courses/original-videos/' . $course->id . '/modules/' . $module->id . '/lessons/' . $lessonData['id'] . '.mp4';
                            $hlsBasePath = 'courses/hls/' . $course->id . '/modules/' . $module->id . '/lessons/' . $lessonData['id'];
                            $awsEndpoint = env('AWS_ENDPOINT');
                            $awsBucket = env('AWS_BUCKET');
                            
                            // Set the final HLS master playlist URL
                            $lessonData['content_url'] = $awsEndpoint . '/' . $awsBucket . '/' . $hlsBasePath . '/master.m3u8';
                            
                            // Generate upload URL for original video file
                            $videoUploadUrl = Storage::disk('s3')->temporaryUrl(
                                $originalVideoPath,
                                now()->addMinutes(120), // 2 hours to upload video
                                ['ContentType' => 'video/mp4']
                            );
                            
                            $videoUploadUrls["module_{$moduleIndex}_lesson_{$lessonIndex}"] = [
                                'upload_url' => $videoUploadUrl,
                                'lesson_id' => $lessonData['id'],
                                'original_video_path' => $originalVideoPath,
                                'hls_base_path' => $hlsBasePath
                            ];
                        }
                        
                        $this->lessonRepository->create($lessonData);
                    }

                    // Create quizzes and questions
                    foreach ($quizzes as $quizData) {
                        $questions = $quizData['questions'] ?? [];
                        unset($quizData['questions']);

                        $quizData['id'] = Str::uuid();
                        $quizData['course_id'] = $course->id;

                        $quiz = $this->quizRepository->create($quizData);

                        // Create questions
                        foreach ($questions as $questionData) {
                            $questionData['id'] = Str::uuid();
                            $questionData['quiz_id'] = $quiz->id;
                            $this->questionRepository->create($questionData);
                        }
                    }
                }
            }

            \DB::commit();

            return [
                'course' => $course->load(['tags', 'modules.lessons', 'quizzes.questions']),
                'thumbnail_upload_url' => $thumbnailUploadUrl,
                'video_upload_urls' => $videoUploadUrls
            ];

        } catch (\Exception $e) {
            \DB::rollback();
            throw $e;
        }
    }
    public function updateCourse($id, array $data) {
        return $this->courseRepository->update($id, $data);
    }
    public function deleteCourse($id) {
        return $this->courseRepository->delete($id);
    }
    public function addReview($studentId, $courseId, $rating, $comment = null) {
        $review = Review::updateOrCreate(
            ['student_id' => $studentId, 'course_id' => $courseId], 
            ['rating' => $rating, 'comment' => $comment]
        );

        // Update course stats
        $course = $this->courseRepository->getById($courseId);
        $allReviews = $course->reviews();
        $course->average_rating = $allReviews->avg('rating');
        $course->review_count = $allReviews->count();
        $course->save();

        return $review;
    }
    public function getModulesWithLessons($id) {
        return $this->courseRepository->getModulesWithLessons($id);
    }
    
    public function searchCourses($query, $limit = 10) {
        $courses = $this->courseRepository->searchCourses($query, $limit);
        return [
            'data' => $courses,
            'total' => $courses->count(),
            'query' => $query
        ];
    }

    public function enroll($courseId, $studentId) {
        $existing = $this->enrollmentRepository->getByCourseAndStudent($courseId, $studentId);
        if ($existing) {
            return ['exists' => true, 'enrollment' => $existing];
        }
        $enrollment = $this->enrollmentRepository->create([
            'id' => Str::uuid(),
            'student_id' => $studentId,
            'course_id' => $courseId,
            'status' => 'ACTIVE',
            'progress' => 0,
        ]);
        return ['exists' => false, 'enrollment' => $enrollment];
    }

    public function getEnrolledStudents($courseId) {
        return $this->enrollmentRepository->getByCourseId($courseId);
    }

    public function uploadLessonVideo($videoPath, $videoFile) {
        try {
            // Validate the file
            if (!$videoFile || !in_array($videoFile->getClientOriginalExtension(), ['mp4', 'mov', 'avi', 'mkv'])) {
                throw new \Exception('Only video files (MP4, MOV, AVI, MKV) are allowed');
            }

            // Store the original video temporarily in local storage for processing
            $tempVideoPath = 'temp/videos/' . uniqid() . '.' . $videoFile->getClientOriginalExtension();
            Storage::disk('local')->put($tempVideoPath, file_get_contents($videoFile));
            
            // Upload original video to S3
            $uploaded = Storage::disk('s3')->put($videoPath, file_get_contents($videoFile));
            
            if (!$uploaded) {
                throw new \Exception('Failed to upload original video file');
            }

            return [
                'success' => true,
                'original_uploaded' => true,
                'temp_path' => $tempVideoPath,
                'message' => 'Original video uploaded successfully. HLS processing will begin shortly.'
            ];

        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Process uploaded video into HLS format
     *
     * @param string $tempVideoPath Path to temporary video file in local storage
     * @param string $hlsBasePath Base path for HLS output in S3
     * @param string $lessonId Lesson ID for updating the content URL
     * @return array Processing result
     */
    public function processVideoToHls($tempVideoPath, $hlsBasePath, $lessonId) {
        try {
            // Get the full local path
            $localVideoPath = Storage::disk('local')->path($tempVideoPath);
            
            // Process video to HLS
            $result = $this->hlsVideoService->processVideoToHls($localVideoPath, $hlsBasePath);
            
            if ($result['success']) {
                // Update lesson with HLS master playlist URL
                $this->updateLessonVideo($lessonId, $result['master_playlist']);
                
                // Clean up temporary file
                Storage::disk('local')->delete($tempVideoPath);
                
                return [
                    'success' => true,
                    'master_playlist' => $result['master_playlist'],
                    'qualities_generated' => $result['qualities_generated'],
                    'message' => 'Video processed successfully into HLS format'
                ];
            } else {
                // Clean up temporary file even on failure
                Storage::disk('local')->delete($tempVideoPath);
                
                throw new \Exception('HLS processing failed: ' . $result['error']);
            }

        } catch (\Exception $e) {
            // Clean up temporary file on error
            if (Storage::disk('local')->exists($tempVideoPath)) {
                Storage::disk('local')->delete($tempVideoPath);
            }
            throw $e;
        }
    }

    public function updateLessonVideo($lessonId, $videoUrl) {
        return $this->lessonRepository->update($lessonId, ['content_url' => $videoUrl]);
    }

    /**
     * Dispatch HLS processing job for a lesson video
     *
     * @param string $tempVideoPath Temporary video file path
     * @param string $hlsBasePath HLS output base path
     * @param string $lessonId Lesson ID
     * @param string|null $originalVideoPath Optional original video path in S3
     * @return void
     */
    public function dispatchHlsProcessing($tempVideoPath, $hlsBasePath, $lessonId, $originalVideoPath = null) {
        \App\Jobs\ProcessVideoToHlsJob::dispatch($tempVideoPath, $hlsBasePath, $lessonId, $originalVideoPath);
    }
}