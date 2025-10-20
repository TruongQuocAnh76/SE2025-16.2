<?php namespace App\Repositories;
use App\Models\Progress;

class ProgressRepository {
    protected $model;
    public function __construct(Progress $model) { $this->model = $model; }
    public function getByStudentAndLesson($studentId, $lessonId) {
        return $this->model->where('student_id', $studentId)->where('lesson_id', $lessonId)->first();
    }
    public function getByStudentId($studentId) {
        return $this->model->where('student_id', $studentId)->get();
    }
    public function create(array $data) {
        return $this->model->create($data);
    }
    public function update($id, array $data) {
        $progress = $this->model->findOrFail($id);
        $progress->update($data);
        return $progress;
    }
    public function updateOrCreate($attributes, $values) {
        return $this->model->updateOrCreate($attributes, $values);
    }
}