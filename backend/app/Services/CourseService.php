<?php namespace App\Services;
use App\Repositories\CourseRepository;
use App\Models\Review;
use Illuminate\Support\Str;

class CourseService {
    protected $courseRepository;
    public function __construct(CourseRepository $courseRepository) { $this->courseRepository = $courseRepository; }
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
        return Review::updateOrCreate(['student_id' => $studentId, 'course_id' => $courseId], ['rating' => $rating, 'comment' => $comment]);
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
}