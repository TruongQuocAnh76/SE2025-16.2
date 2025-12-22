<?php

namespace App\Services;

use App\Models\Answer;
use App\Models\QuizAttempt;
use Illuminate\Support\Facades\Log;

class GradingService
{
    /**
     * Auto-grade all answers in a quiz attempt.
     *
     * @param \App\Models\QuizAttempt $attempt
     * @return array Returns grading summary
     */
    public function autoGradeAttempt(QuizAttempt $attempt): array
    {
        if (!$attempt->isCompleted()) {
            throw new \Exception('Cannot grade an incomplete attempt');
        }

        $answers = $attempt->answers()->with('question')->get();
        $autoGradedCount = 0;

        foreach ($answers as $answer) {
            if ($answer->question->isAutoGradable() && !$answer->isGraded()) {
                $answer->autoGrade();
                $autoGradedCount++;
            }
        }

        // Recalculate total score
        $this->calculateTotalScore($attempt);
        $this->updateAttemptStatus($attempt);

        Log::info('Auto-graded quiz attempt', [
            'attempt_id' => $attempt->id,
            'student_id' => $attempt->student_id,
            'quiz_id' => $attempt->quiz_id,
            'score' => $attempt->score,
            'auto_graded_count' => $autoGradedCount,
        ]);

        return [
            'auto_graded' => $autoGradedCount,
            'total_answers' => $answers->count(),
            'score' => $attempt->score,
            'is_passed' => $attempt->is_passed,
        ];
    }

    /**
     * Manually grade an answer.
     *
     * @param \App\Models\Answer $answer
     * @param int $pointsAwarded
     * @param bool|null $isCorrect
     * @return bool
     */
    public function manualGrade(Answer $answer, int $pointsAwarded, ?bool $isCorrect = null): bool
    {
        // Validate points don't exceed question's max points
        $maxPoints = $answer->question->points;

        if ($pointsAwarded > $maxPoints) {
            throw new \InvalidArgumentException(
                "Points awarded ({$pointsAwarded}) cannot exceed question's max points ({$maxPoints})"
            );
        }

        if ($pointsAwarded < 0) {
            throw new \InvalidArgumentException("Points awarded cannot be negative");
        }

        $result = $answer->manualGrade($pointsAwarded, $isCorrect);

        if ($result) {
            // Recalculate total score for the attempt
            $this->calculateTotalScore($answer->attempt);
            $this->updateAttemptStatus($answer->attempt);

            Log::info('Manually graded answer', [
                'answer_id' => $answer->id,
                'attempt_id' => $answer->attempt_id,
                'points_awarded' => $pointsAwarded,
                'is_correct' => $answer->is_correct,
            ]);
        }

        return $result;
    }

    /**
     * Calculate total score for a quiz attempt.
     *
     * @param \App\Models\QuizAttempt $attempt
     * @return float
     */
    public function calculateTotalScore(QuizAttempt $attempt): float
    {
        $totalPoints = $attempt->quiz->getTotalPoints();

        if ($totalPoints === 0) {
            $attempt->score = 0;
            $attempt->save();
            return 0;
        }

        $earnedPoints = $attempt->answers()->sum('points_awarded');
        $score = round(($earnedPoints / $totalPoints) * 100, 2);

        $attempt->score = $score;
        $attempt->save();

        return $score;
    }

    /**
     * Update attempt status based on passing score.
     *
     * @param \App\Models\QuizAttempt $attempt
     * @return bool
     */
    public function updateAttemptStatus(QuizAttempt $attempt): bool
    {
        $isPassed = $attempt->score >= $attempt->quiz->passing_score;

        if ($attempt->is_passed !== $isPassed) {
            $attempt->is_passed = $isPassed;
            return $attempt->save();
        }

        return true;
    }

    /**
     * Get grading statistics for a quiz attempt.
     *
     * @param \App\Models\QuizAttempt $attempt
     * @return array
     */
    public function getQuizGradingStats(QuizAttempt $attempt): array
    {
        $answers = $attempt->answers()->with('question')->get();

        $autoGraded = $answers->filter(function ($answer) {
            return $answer->question->isAutoGradable();
        })->count();

        $manualGraded = $answers->filter(function ($answer) {
            return !$answer->question->isAutoGradable();
        })->count();

        $correctAnswers = $answers->where('is_correct', true)->count();
        $incorrectAnswers = $answers->where('is_correct', false)->count();
        $ungradedAnswers = $answers->filter(function ($answer) {
            return $answer->is_correct === null;
        })->count();

        $totalQuestions = $answers->count();

        $stats = [
            'total_questions' => $totalQuestions,
            'auto_graded' => $autoGraded,
            'manual_graded' => $manualGraded,
            'correct_answers' => $correctAnswers,
            'incorrect_answers' => $incorrectAnswers,
            'ungraded_answers' => $ungradedAnswers,
            'accuracy' => $totalQuestions > 0
                ? round(($correctAnswers / $totalQuestions) * 100, 2)
                : 0,
        ];

        return $stats;
    }

    /**
     * Get detailed grading breakdown for an attempt.
     *
     * @param \App\Models\QuizAttempt $attempt
     * @return array
     */
    public function getGradingBreakdown(QuizAttempt $attempt): array
    {
        $answers = $attempt->answers()->with('question')->get();

        $breakdown = [];

        foreach ($answers as $answer) {
            $breakdown[] = [
                'question_id' => $answer->question_id,
                'question_text' => $answer->question->question_text,
                'question_type' => $answer->question->question_type,
                'points_possible' => $answer->question->points,
                'points_awarded' => $answer->points_awarded,
                'is_correct' => $answer->is_correct,
                'is_auto_graded' => $answer->question->isAutoGradable(),
                'answer_text' => $answer->getFormattedAnswer(),
                'correct_answer' => $answer->question->getCorrectAnswerForDisplay(),
                'explanation' => $answer->question->explanation,
            ];
        }

        return $breakdown;
    }

    /**
     * Bulk grade multiple answers.
     *
     * @param array $gradingData
     * @return array
     */
    public function bulkGradeAnswers(array $gradingData): array
    {
        $results = [];

        foreach ($gradingData as $data) {
            try {
                $answer = Answer::findOrFail($data['answer_id']);

                $this->manualGrade(
                    $answer,
                    $data['points_awarded'],
                    $data['is_correct'] ?? null
                );

                $results[] = [
                    'answer_id' => $data['answer_id'],
                    'success' => true,
                    'message' => 'Graded successfully',
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'answer_id' => $data['answer_id'] ?? null,
                    'success' => false,
                    'message' => $e->getMessage(),
                ];
            }
        }

        // Check if all answers are now graded
        $attemptId = Answer::find($gradingData[0]['answer_id'])->attempt_id;
        $attempt = QuizAttempt::find($attemptId);
        
        $pendingAnswersCount = Answer::where('attempt_id', $attemptId)
            ->whereIn('question_id', function ($query) {
                $query->select('id')->from('questions')->whereIn('question_type', ['SHORT_ANSWER', 'ESSAY']);
            })
            ->whereNull('is_correct') // Assuming is_correct null means ungraded
            ->count();
            
        if ($pendingAnswersCount === 0) {
           $attempt->grading_status = 'graded';
           $attempt->save();
        }

        return $results;
    }

    /**
     * Get all answers that need manual grading for a quiz.
     *
     * @param string $quizId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAnswersNeedingManualGrading(string $quizId)
    {
        return Answer::whereHas('attempt', function ($query) use ($quizId) {
            $query->where('quiz_id', $quizId)
                ->whereNotNull('submitted_at');
        })
            ->whereHas('question', function ($query) {
                $query->whereIn('question_type', ['SHORT_ANSWER', 'ESSAY']);
            })
            ->whereNull('is_correct')
            ->with(['question', 'attempt.student'])
            ->get();
    }

    /**
     * Re-grade all auto-gradable answers (useful if correct_answer changes).
     *
     * @param \App\Models\QuizAttempt $attempt
     * @return int Returns number of re-graded answers
     */
    public function reGradeAutoGradableAnswers(QuizAttempt $attempt): int
    {
        $answers = $attempt->answers()
            ->whereHas('question', function ($query) {
                $query->whereIn('question_type', ['MULTIPLE_CHOICE', 'TRUE_FALSE']);
            })
            ->with('question')
            ->get();

        $count = 0;
        foreach ($answers as $answer) {
            if ($answer->autoGrade()) {
                $count++;
            }
        }

        // Recalculate total score
        $this->calculateTotalScore($attempt);
        $this->updateAttemptStatus($attempt);

        Log::info('Re-graded auto-gradable answers', [
            'attempt_id' => $attempt->id,
            're_graded_count' => $count,
        ]);

        return $count;
    }
}
