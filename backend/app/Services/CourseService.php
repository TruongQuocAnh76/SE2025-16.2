<?php namespace App\Services;
use App\Repositories\CourseRepository;
use App\Repositories\EnrollmentRepository;
use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Str;

class CourseService {
    protected $courseRepository;
    protected $enrollmentRepository;

    public function __construct(CourseRepository $courseRepository, EnrollmentRepository $enrollmentRepository) { 
        $this->courseRepository = $courseRepository;
        $this->enrollmentRepository = $enrollmentRepository;
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
}