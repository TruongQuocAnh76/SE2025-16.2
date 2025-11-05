<?php namespace App\Repositories;
use App\Models\Quiz;

class QuizRepository {
    protected $model;
    public function __construct(Quiz $model) { $this->model = $model; }
    public function getByCourseId($courseId) {
        return $this->model->where('course_id', $courseId)->withCount('questions')->orderBy('order_index')->get();
    }
    public function getById($id) {
        return $this->model->with('questions')->findOrFail($id);
    }
    public function create(array $data) {
        return $this->model->create($data);
    }
    public function update($id, array $data) {
        $quiz = $this->model->findOrFail($id);
        $quiz->update($data);
        return $quiz;
    }
    public function delete($id) {
        return $this->model->findOrFail($id)->delete();
    }
}
