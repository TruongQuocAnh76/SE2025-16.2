<?php namespace App\Repositories;
use App\Models\QuizAttempt;

class QuizAttemptRepository {
    protected $model;
    public function __construct(QuizAttempt $model) { $this->model = $model; }
    public function getAll() {
        return $this->model->all();
    }
    public function getById($id) {
        return $this->model->findOrFail($id);
    }
    public function create(array $data) {
        return $this->model->create($data);
    }
    public function update($id, array $data) {
        $attempt = $this->model->findOrFail($id);
        $attempt->update($data);
        return $attempt;
    }
    public function getByStudentAndQuiz($studentId, $quizId) {
        return $this->model->where('student_id', $studentId)->where('quiz_id', $quizId)->count();
    }
    public function getHistoryByQuiz($studentId, $quizId) {
        return $this->model->where('student_id', $studentId)->where('quiz_id', $quizId)->orderByDesc('created_at')->get();
    }
}