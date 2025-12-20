<?php namespace App\Services;
use App\Repositories\CourseRepository;
use App\Repositories\EnrollmentRepository;
use App\Repositories\ModuleRepository;
use App\Repositories\LessonRepository;
use App\Repositories\QuizRepository;
use App\Repositories\QuestionRepository;
use App\Models\Review;
use App\Models\User;
use App\Services\HlsVideoService;
use App\Contracts\StorageServiceInterface;
use App\Helpers\StorageUrlHelper;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class CourseService {
    protected $courseRepository;
    protected $enrollmentRepository;
    protected $moduleRepository;
    protected $lessonRepository;
    protected $quizRepository;
    protected $questionRepository;
    protected $hlsVideoService;
    protected StorageServiceInterface $storage;

    public function __construct(
        CourseRepository $courseRepository,
        EnrollmentRepository $enrollmentRepository,
        ModuleRepository $moduleRepository,
        LessonRepository $lessonRepository,
        QuizRepository $quizRepository,
        QuestionRepository $questionRepository,
        HlsVideoService $hlsVideoService,
        StorageServiceInterface $storage
    ) {
        $this->courseRepository = $courseRepository;
        $this->enrollmentRepository = $enrollmentRepository;
        $this->moduleRepository = $moduleRepository;
        $this->lessonRepository = $lessonRepository;
        $this->quizRepository = $quizRepository;
        $this->questionRepository = $questionRepository;
        $this->hlsVideoService = $hlsVideoService;
        $this->storage = $storage;
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
        DB::beginTransaction();

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

            // Handle thumbnail - only generate upload URL if thumbnail is requested
            $thumbnailUploadUrl = null;
            if (empty($data['thumbnail'])) {
                // Use default placeholder thumbnail instead of generating upload URL
                $data['thumbnail'] = '/placeholder-course.jpg';
            } else if ($data['thumbnail'] === 'UPLOAD_REQUESTED') {
                // Frontend specifically requested thumbnail upload
                $thumbnailPath = 'courses/thumbnails/' . $data['id'] . '.jpg';
                
                // Use frontend-accessible endpoint
                $data['thumbnail'] = StorageUrlHelper::buildFullUrl($thumbnailPath);
                
                $thumbnailUploadUrl = $this->storage->temporaryUploadUrl(
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
                            
                            $lessonData['content_url'] = StorageUrlHelper::buildFullUrl($hlsBasePath . '/master.m3u8');
                            
                            // Generate upload URL for original video file
                            $videoUploadUrl = $this->storage->temporaryUploadUrl(
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

            DB::commit();

            return [
                'course' => $course->load(['tags', 'modules.lessons', 'quizzes.questions']),
                'thumbnail_upload_url' => $thumbnailUploadUrl,
                'video_upload_urls' => $videoUploadUrls
            ];

        } catch (\Exception $e) {
            DB::rollback();
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

    /**
     * Get course recommendations for a user.
     *
     * @param User $user
     * @return \Illuminate\Database\Eloquent\Collection|array
     */
    public function getRecommendations(User $user)
    {
        // Check if user has any enrollments
        if ($user->enrollments->isEmpty()) {
            // New user: return popular courses
            return $this->courseRepository->getPopularCourses();
        }

        // Existing user: return content-based recommendations
        return $this->courseRepository->getContentBasedRecommendations($user);
    }

    public function updateLessonVideo($lessonId, $videoUrl) {
        return $this->lessonRepository->update($lessonId, ['content_url' => $videoUrl]);
    }

    /**
     * Dispatch HLS processing job for a lesson video
     *
     * @param string|null $tempVideoPath Temporary video file path (null if already in S3)
     * @param string $hlsBasePath HLS output base path
     * @param string $lessonId Lesson ID
     * @param string|null $originalVideoPath Original video path in S3
     * @return void
     */
    public function dispatchHlsProcessing($tempVideoPath, $hlsBasePath, $lessonId, $originalVideoPath = null) {
        \App\Jobs\ProcessVideoToHlsJob::dispatch($tempVideoPath, $hlsBasePath, $lessonId, $originalVideoPath);
    }

    /**
     * Handle video upload completion notification
     * This method is called when the frontend has successfully uploaded a video to S3
     *
     * @param string $lessonId Lesson ID
     * @param string $originalVideoPath Path to original video in S3
     * @param string $hlsBasePath Base path for HLS output
     * @param array $metadata Optional metadata (size, duration, etc.)
     * @return array Processing result
     */
    public function handleVideoUploadComplete($lessonId, $originalVideoPath, $hlsBasePath, $metadata = []) {
        try {
            // Verify the lesson exists
            $lesson = $this->lessonRepository->getById($lessonId);
            if (!$lesson) {
                throw new \Exception('Lesson not found');
            }

            // Verify the video file exists in storage
            if (!$this->storage->exists($originalVideoPath)) {
                throw new \Exception('Original video file not found in storage');
            }

            // Update lesson metadata if provided
            if (!empty($metadata)) {
                $updateData = [];
                if (isset($metadata['video_duration'])) {
                    $updateData['duration'] = $metadata['video_duration'];
                }
                
                if (!empty($updateData)) {
                    $this->lessonRepository->update($lessonId, $updateData);
                }
            }

            // Dispatch HLS processing job
            $this->dispatchHlsProcessing(null, $hlsBasePath, $lessonId, $originalVideoPath);

            // Generate expected HLS URL
            $expectedHlsUrl = StorageUrlHelper::buildFullUrl($hlsBasePath . '/master.m3u8');

            return [
                'success' => true,
                'lesson_id' => $lessonId,
                'original_video_path' => $originalVideoPath,
                'expected_hls_url' => $expectedHlsUrl,
                'processing_status' => 'started',
                'message' => 'HLS processing job dispatched successfully'
            ];

        } catch (\Exception $e) {
            throw $e;
        }
    }
}