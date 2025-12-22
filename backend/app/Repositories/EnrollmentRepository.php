<?php namespace App\Repositories;
use App\Models\Enrollment;

class EnrollmentRepository {
    protected $model;
    public function __construct(Enrollment $model) { $this->model = $model; }
    
    public function getAll($perPage = 20) {
        return $this->model->with(['student:id,first_name,last_name', 'course:id,title'])->paginate($perPage);
    }
    
    public function getById($id) {
        return $this->model->findOrFail($id);
    }
    
    public function create(array $data) {
        return $this->model->create($data);
    }
    
    public function update($id, array $data) {
        $enrollment = $this->model->findOrFail($id);
        $enrollment->update($data);
        return $enrollment;
    }
    
    public function delete($id) {
        return $this->model->findOrFail($id)->delete();
    }
    
    public function getByCourseAndStudent($courseId, $studentId) {
        return $this->model->where('course_id', $courseId)->where('student_id', $studentId)->first();
    }
    
    public function getByCourseId($courseId) {
        return $this->model->where('course_id', $courseId)->with('student:id,first_name,last_name,email')->get();
    }
    
    public function getByStudentId($studentId) {
        return $this->model->where('student_id', $studentId)->with('course:id,title')->get();
    }

    public function getByStudentAndCourse($studentId, $courseId) {
        return $this->model->where('student_id', $studentId)->where('course_id', $courseId)->first();
    }
}