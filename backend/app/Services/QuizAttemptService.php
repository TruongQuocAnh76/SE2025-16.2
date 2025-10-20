<?php namespace App\Services;
use App\Repositories\QuizAttemptRepository;
use App\Models\Answer;
use Illuminate\Support\Str;

class QuizAttemptService {
    protected $attemptRepository;
    public function __construct(QuizAttemptRepository $attemptRepository) {
        $this->attemptRepository = $attemptRepository;
    }
    public function startAttempt($studentId, $quizId) {
        $count = $this->attemptRepository->getByStudentAndQuiz($studentId, $quizId);
        $data = ['id' => Str::uuid(), 'student_id' => $studentId, 'quiz_id' => $quizId, 'attempt_number' => $count + 1, 'started_at' => now()];
        return $this->attemptRepository->create($data);
    }
    public function submitAttemptWithDetails($attemptId, $answers) {
        $attempt = $this->attemptRepository->getById($attemptId);
        $quiz = $attempt->quiz()->with('questions')->first();
        $totalPoints = 0;
        $earnedPoints = 0;
        foreach ($quiz->questions as $q) {
            $totalPoints += $q->points;
            $studentAnswer = $answers[$q->id] ?? null;
            $isCorrect = false;
            $pointsAwarded = 0;
            if ($studentAnswer !== null && in_array($q->question_type, ['MULTIPLE_CHOICE', 'TRUE_FALSE'])) {
                $isCorrect = trim($studentAnswer) == trim($q->correct_answer);
                $pointsAwarded = $isCorrect ? $q->points : 0;
            }
            Answer::create(['id' => Str::uuid(), 'answer_text' => $studentAnswer, 'is_correct' => $isCorrect, 'points_awarded' => $pointsAwarded, 'attempt_id' => $attemptId, 'question_id' => $q->id]);
            $earnedPoints += $pointsAwarded;
        }
        $scorePercent = ($totalPoints > 0) ? ($earnedPoints / $totalPoints * 100) : 0;
        $isPassed = $scorePercent >= $quiz->passing_score;
        return $this->attemptRepository->update($attemptId, ['score' => round($scorePercent, 2), 'is_passed' => $isPassed, 'submitted_at' => now()]);
    }
    public function getAttemptHistory($studentId, $quizId) {
        return $this->attemptRepository->getHistoryByQuiz($studentId, $quizId);
    }
}