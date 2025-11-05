<?php namespace App\Services;
use App\Repositories\CourseRepository;
use App\Repositories\EnrollmentRepository;
use App\Repositories\ModuleRepository;
use App\Repositories\LessonRepository;
use App\Repositories\QuizRepository;
use App\Repositories\QuestionRepository;
use App\Models\Review;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class CourseService {
    protected $courseRepository;
    protected $enrollmentRepository;
    protected $moduleRepository;
    protected $lessonRepository;
    protected $quizRepository;
    protected $questionRepository;

    public function __construct(
        CourseRepository $courseRepository,
        EnrollmentRepository $enrollmentRepository,
        ModuleRepository $moduleRepository,
        LessonRepository $lessonRepository,
        QuizRepository $quizRepository,
        QuestionRepository $questionRepository
    ) {
        $this->courseRepository = $courseRepository;
        $this->enrollmentRepository = $enrollmentRepository;
        $this->moduleRepository = $moduleRepository;
        $this->lessonRepository = $lessonRepository;
        $this->quizRepository = $quizRepository;
        $this->questionRepository = $questionRepository;
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
                        
                        // Generate video upload URL and final storage path
                        if (empty($lessonData['content_url'])) {
                            $videoPath = 'courses/videos/' . $course->id . '/modules/' . $module->id . '/lessons/' . $lessonData['id'] . '.mp4';
                            $awsEndpoint = env('AWS_ENDPOINT');
                            $awsBucket = env('AWS_BUCKET');
                            
                            // Set the final video URL
                            $lessonData['content_url'] = $awsEndpoint . '/' . $awsBucket . '/' . $videoPath;
                            
                            // Generate upload URL for the frontend
                            $videoUploadUrl = Storage::disk('s3')->temporaryUrl(
                                $videoPath,
                                now()->addMinutes(60), // 1 hour to upload video
                                ['ContentType' => 'video/mp4']
                            );
                            
                            $videoUploadUrls["module_{$moduleIndex}_lesson_{$lessonIndex}"] = [
                                'upload_url' => $videoUploadUrl,
                                'lesson_id' => $lessonData['id'],
                                'video_path' => $videoPath
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
            if (!$videoFile || $videoFile->getClientOriginalExtension() !== 'mp4') {
                throw new \Exception('Only MP4 files are allowed');
            }
            // Upload to S3 (LocalStack)
            $uploaded = Storage::disk('s3')->put($videoPath, file_get_contents($videoFile));
            
            if (!$uploaded) {
                throw new \Exception('Failed to upload video file');
            }

            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function updateLessonVideo($lessonId, $videoUrl) {
        return $this->lessonRepository->update($lessonId, ['content_url' => $videoUrl]);
    }
}