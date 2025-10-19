<?php namespace App\Services;
use App\Repositories\QuizRepository;
use Illuminate\Support\Str;

class QuizService {
    protected $quizRepository;
    public function __construct(QuizRepository $quizRepository) { $this->quizRepository = $quizRepository; }
    public function getQuizzesByCourse($courseId) {
        return $this->quizRepository->getByCourseId($courseId);
    }
    public function getQuizById($id) {
        return $this->quizRepository->getById($id);
    }
    public function createQuiz(array $data) {
        $data['id'] = Str::uuid();
        $data['is_active'] = true;
        return $this->quizRepository->create($data);
    }
    public function updateQuiz($id, array $data) {
        return $this->quizRepository->update($id, $data);
    }
    public function deleteQuiz($id) {
        return $this->quizRepository->delete($id);
    }
}
