<?php

namespace App\Services;

use App\Repositories\LessonRepository;
use App\Repositories\CourseRepository;
use App\Repositories\ModuleRepository;
use App\Repositories\ProgressRepository;
use App\Repositories\EnrollmentRepository;
use App\Contracts\StorageServiceInterface;
use Illuminate\Support\Facades\Auth;

class LessonService
{
    protected $lessonRepository;
    protected $courseRepository;
    protected $moduleRepository;
    protected $progressRepository;
    protected $enrollmentRepository;
    protected $storage;

    public function __construct(
        LessonRepository $lessonRepository,
        CourseRepository $courseRepository,
        ModuleRepository $moduleRepository,
        ProgressRepository $progressRepository,
        EnrollmentRepository $enrollmentRepository,
        StorageServiceInterface $storage
    ) {
        $this->lessonRepository = $lessonRepository;
        $this->courseRepository = $courseRepository;
        $this->moduleRepository = $moduleRepository;
        $this->progressRepository = $progressRepository;
        $this->enrollmentRepository = $enrollmentRepository;
        $this->storage = $storage;
    }

    /**
     * Get lesson by ID with module relationship
     */
    public function getLessonById($lessonId)
    {
        return $this->lessonRepository->getByIdWithModule($lessonId);
    }

    /**
     * Get lessons by module ID
     */
    public function getLessonsByModuleId($moduleId)
    {
        return $this->lessonRepository->getByModuleId($moduleId);
    }

    /**
     * Create a new lesson
     */
    public function createLesson(string $courseId, array $data, $videoFile = null)
    {
        // Get or create default module
        $moduleId = $data['module_id'] ?? null;
        if (!$moduleId) {
            $defaultModule = $this->moduleRepository->firstOrCreate(
                ['course_id' => $courseId, 'title' => 'Main Content'],
                ['order_index' => 1]
            );
            $moduleId = $defaultModule->id;
        }

        // Handle video upload
        $videoUrl = null;
        $contentType = $data['content_type'] ?? 'TEXT'; // Default to TEXT
        if ($videoFile) {
            $videoPath = $videoFile->store('lessons/videos', 'public');
            $videoUrl = \Illuminate\Support\Facades\Storage::url($videoPath);
            $contentType = 'VIDEO'; // Set to VIDEO when video is uploaded
        }

        // Get next order index
        $orderIndex = $data['order'] ?? ($this->lessonRepository->getMaxOrderIndex($moduleId) + 1);

        return $this->lessonRepository->create([
            'module_id' => $moduleId,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'text_content' => $data['content'] ?? null,
            'content_type' => $contentType,
            'content_url' => $videoUrl,
            'order_index' => $orderIndex,
            'duration' => $data['duration'] ?? null
        ]);
    }

    /**
     * Update a lesson
     */
    public function updateLesson(string $lessonId, array $data, $videoFile = null)
    {
        $lesson = $this->lessonRepository->getById($lessonId);
        
        if (!$lesson) {
            return null;
        }

        // Handle video upload
        $contentType = $lesson->content_type;
        if ($videoFile) {
            // Delete old video if exists
            if ($lesson->content_url) {
                $oldPath = str_replace('/storage/', '', $lesson->content_url);
                \Illuminate\Support\Facades\Storage::disk('public')->delete($oldPath);
            }
            $videoPath = $videoFile->store('lessons/videos', 'public');
            $data['content_url'] = \Illuminate\Support\Facades\Storage::url($videoPath);
            $contentType = 'VIDEO';
        } else if (isset($data['content_url']) && $data['content_url'] === '') {
            // Nếu xóa video
            $data['content_url'] = null;
            $contentType = 'TEXT';
        }

        // Filter null values and rename order to order_index
        $updateData = array_filter([
            'title' => $data['title'] ?? null,
            'description' => $data['description'] ?? null,
            'text_content' => $data['content'] ?? null,
            'module_id' => $data['module_id'] ?? null,
            'order_index' => $data['order'] ?? null,
            'duration' => $data['duration'] ?? null,
            'content_url' => $data['content_url'] ?? null,
            'content_type' => $contentType
        ], fn($value) => $value !== null);

        return $this->lessonRepository->update($lessonId, $updateData);
    }

    /**
     * Delete a lesson
     */
    public function deleteLesson(string $lessonId)
    {
        $lesson = $this->lessonRepository->getById($lessonId);
        
        if (!$lesson) {
            return false;
        }

        // Delete video file if exists
        if ($lesson->video_url) {
            $path = str_replace('/storage/', '', $lesson->video_url);
            \Illuminate\Support\Facades\Storage::disk('public')->delete($path);
        }

        return $this->lessonRepository->delete($lessonId);
    }

    /**
     * Check if user can manage lesson (is teacher of course or admin)
     */
    public function canManageLesson(string $courseId, $user): bool
    {
        $course = $this->courseRepository->getById($courseId);
        
        if (!$course) {
            return false;
        }

        return $course->teacher_id === $user->id || $user->role === 'ADMIN';
    }

    /**
     * Get lesson progress for a user
     */
    public function getLessonProgress(string $lessonId, string $userId)
    {
        $lesson = $this->lessonRepository->getById($lessonId);
        
        if (!$lesson) {
            return null;
        }

        $progress = $this->progressRepository->getByStudentAndLesson($userId, $lessonId);

        return $progress ?: [
            'lesson_id' => $lessonId,
            'student_id' => $userId,
            'is_completed' => false,
            'time_spent' => 0,
            'completed_at' => null
        ];
    }

    /**
     * Check if user is enrolled in a course
     */
    public function checkEnrollment(string $courseId, string $userId)
    {
        return $this->enrollmentRepository->getByStudentAndCourse($userId, $courseId);
    }

    /**
     * Get comments for a lesson
     */
    public function getComments(string $lessonId)
    {
        $lesson = $this->lessonRepository->getById($lessonId);
        
        if (!$lesson) {
            return null;
        }

        return $this->lessonRepository->getCommentsByLessonId($lessonId);
    }

    /**
     * Add a comment to a lesson
     */
    public function addComment(string $lessonId, string $userId, string $content, ?string $parentId = null)
    {
        $lesson = $this->lessonRepository->getById($lessonId);
        
        if (!$lesson) {
            return null;
        }

        return $this->lessonRepository->createComment([
            'lesson_id' => $lessonId,
            'user_id' => $userId,
            'content' => $content,
            'parent_id' => $parentId
        ]);
    }

    /**
     * Get course by ID
     */
    public function getCourseById(string $courseId)
    {
        return $this->courseRepository->getById($courseId);
    }
}
