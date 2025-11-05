<?php namespace App\Services;
use App\Repositories\EnrollmentRepository;
use Illuminate\Support\Str;

class EnrollmentService {
    protected $enrollmentRepository;
    public function __construct(EnrollmentRepository $enrollmentRepository) { $this->enrollmentRepository = $enrollmentRepository; }
    public function getAllEnrollments($perPage = 20) {
        return $this->enrollmentRepository->getAll($perPage);
    }
    public function getEnrollmentById($id) {
        return $this->enrollmentRepository->getById($id);
    }
    public function enrollStudent($courseId, $studentId) {
        $exists = $this->enrollmentRepository->getByCourseAndStudent($courseId, $studentId);
        if ($exists) throw new \Exception('Already enrolled', 409);
        $data = ['id' => Str::uuid(), 'student_id' => $studentId, 'course_id' => $courseId, 'status' => 'ACTIVE', 'progress' => 0];
        return $this->enrollmentRepository->create($data);
    }
    public function updateEnrollment($id, array $data) {
        return $this->enrollmentRepository->update($id, $data);
    }
    public function deleteEnrollment($id) {
        return $this->enrollmentRepository->delete($id);
    }
    public function getStudentsInCourse($courseId) {
        return $this->enrollmentRepository->getByCourseId($courseId);
    }
    public function getStudentCourses($studentId) {
        return $this->enrollmentRepository->getByStudentId($studentId);
    }
}
