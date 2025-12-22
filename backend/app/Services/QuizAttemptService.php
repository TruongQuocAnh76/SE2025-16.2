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
            
            if ($studentAnswer !== null) {
                // Resolve student answer indices to values if necessary
                $resolvedAnswer = $studentAnswer;
                $options = is_string($q->options) ? json_decode($q->options, true) : $q->options;
                
                if (is_array($options)) {
                    // Check if multiple choice or checkbox answer is sending indices
                    if ($q->question_type === 'CHECKBOX') {
                        $indices = is_array($studentAnswer) ? $studentAnswer : explode(',', $studentAnswer);
                        $mappedValues = [];
                        foreach ($indices as $idx) {
                            $idx = trim($idx);
                            if (is_numeric($idx) && isset($options[$idx])) {
                                $mappedValues[] = $options[$idx];
                            } else {
                                $mappedValues[] = $idx;
                            }
                        }
                        $resolvedAnswer = $mappedValues;
                    } elseif (in_array($q->question_type, ['MULTIPLE_CHOICE', 'TRUE_FALSE'])) {
                        $idx = trim($studentAnswer);
                        if (is_numeric($idx) && isset($options[$idx])) {
                            $resolvedAnswer = $options[$idx];
                        }
                    }
                }

                if (in_array($q->question_type, ['MULTIPLE_CHOICE', 'TRUE_FALSE'])) {
                    \Illuminate\Support\Facades\Log::info("Grading Q: {$q->id} Student (Raw): '$studentAnswer' Resolved: '$resolvedAnswer' Correct: '{$q->correct_answer}'");
                    $isCorrect = trim($resolvedAnswer) == trim($q->correct_answer);
                    $pointsAwarded = $isCorrect ? $q->points : 0;
                } elseif ($q->question_type === 'CHECKBOX') {
                    // Logic already works on arrays now that we resolved it
                    $studentAnsArray = is_array($resolvedAnswer) ? $resolvedAnswer : explode(',', $resolvedAnswer);
                    $studentAnsArray = array_map('trim', $studentAnsArray);
                    sort($studentAnsArray);

                    // Correct answer parsing
                    $correctAnsRaw = $q->correct_answer;
                    $correctAnsArray = json_decode($correctAnsRaw, true);
                    if (!is_array($correctAnsArray)) {
                        $correctAnsArray = explode(',', $correctAnsRaw);
                    }
                    $correctAnsArray = array_map('trim', $correctAnsArray);
                    sort($correctAnsArray);

                    // Strict comparison
                    $isCorrect = ($studentAnsArray === $correctAnsArray);
                    $pointsAwarded = $isCorrect ? $q->points : 0;
                    
                     \Illuminate\Support\Facades\Log::info("Grading Q: {$q->id} CHECKBOX. Resolved: " . json_encode($studentAnsArray) . " Correct: " . json_encode($correctAnsArray));
                }
            }
            // Update answer text to stored the RESOLVED value for clarity? 
            // Or keep distinct raw/resolved? Let's store raw in answer_text but maybe add a note? 
            // No, Answer table just has answer_text. Let's keep raw input for audit.
            Answer::create(['id' => Str::uuid(), 'answer_text' => is_array($studentAnswer) ? json_encode($studentAnswer) : $studentAnswer, 'is_correct' => $isCorrect, 'points_awarded' => $pointsAwarded, 'attempt_id' => $attemptId, 'question_id' => $q->id]);
            $earnedPoints += $pointsAwarded;
        }
        $scorePercent = ($totalPoints > 0) ? ($earnedPoints / $totalPoints * 100) : 0;
        $isPassed = $scorePercent >= $quiz->passing_score;
        
        // Determine grading status
        $requiresManualGrading = $quiz->questions()->whereIn('question_type', ['SHORT_ANSWER', 'ESSAY'])->exists();
        $gradingStatus = $requiresManualGrading ? 'pending_manual' : 'graded';

        return $this->attemptRepository->update($attemptId, [
            'score' => round($scorePercent, 2),
            'is_passed' => $isPassed,
            'grading_status' => $gradingStatus,
            'submitted_at' => now()
        ]);
    }
    public function getAttemptHistory($studentId, $quizId) {
        return $this->attemptRepository->getHistoryByQuiz($studentId, $quizId);
    }
    public function getAllAttemptsByQuiz($quizId) {
        return $this->attemptRepository->getAllAttemptsByQuiz($quizId);
    }
}