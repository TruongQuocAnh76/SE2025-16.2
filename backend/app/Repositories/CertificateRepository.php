<?php namespace App\Repositories;
use App\Models\Certificate;

class CertificateRepository {
    protected $model;
    public function __construct(Certificate $model) { $this->model = $model; }
    
    public function getAll() {
        return $this->model->all();
    }
    
    public function getById($id) {
        return $this->model->with(['course', 'student'])->findOrFail($id);
    }
    
    public function create(array $data) {
        return $this->model->create($data);
    }
    
    public function update($id, array $data) {
        $cert = $this->model->findOrFail($id);
        $cert->update($data);
        return $cert;
    }
    
    public function getByStudentId($studentId) {
        return $this->model->where('student_id', $studentId)->with('course:id,title')->orderByDesc('issued_at')->get();
    }
    
    public function getByCertificateNumber($number) {
        return $this->model->where('certificate_number', $number)->first();
    }
    
    public function getByStudentAndCourse($studentId, $courseId) {
        return $this->model->where('student_id', $studentId)->where('course_id', $courseId)->first();
    }
}