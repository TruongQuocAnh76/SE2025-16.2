<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuizAttempt extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'student_id',
        'quiz_id',
        'score',
        'is_passed',
        'attempt_number',
        'time_spent',
        'started_at',
        'submitted_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'score' => 'decimal:2',
        'is_passed' => 'boolean',
        'attempt_number' => 'integer',
        'time_spent' => 'integer',
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
    ];

    /**
     * Get the student who made this attempt.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the quiz for this attempt.
     */
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    /**
     * Get the answers for this attempt.
     */
    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class, 'attempt_id');
    }

    /**
     * Check if the attempt is in progress.
     *
     * @return bool
     */
    public function isInProgress(): bool
    {
        return $this->started_at !== null && $this->submitted_at === null;
    }

    /**
     * Check if the attempt is completed.
     *
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this->submitted_at !== null;
    }

    /**
     * Check if the attempt is timed out.
     *
     * @return bool
     */
    public function isTimedOut(): bool
    {
        if (!$this->isInProgress()) {
            return false;
        }

        if (!$this->quiz->time_limit) {
            return false;
        }

        $timeLimit = $this->quiz->time_limit * 60; // Convert minutes to seconds
        $elapsedTime = now()->diffInSeconds($this->started_at);

        return $elapsedTime > $timeLimit;
    }

    /**
     * Get remaining time in seconds.
     *
     * @return int|null
     */
    public function getRemainingTime(): ?int
    {
        if (!$this->quiz->time_limit) {
            return null; // No time limit
        }

        if (!$this->isInProgress()) {
            return 0;
        }

        $timeLimit = $this->quiz->time_limit * 60; // Convert minutes to seconds
        $elapsedTime = now()->diffInSeconds($this->started_at);
        $remaining = $timeLimit - $elapsedTime;

        return max(0, (int) $remaining);
    }

    /**
     * Calculate the score for this attempt.
     *
     * @return float
     */
    public function calculateScore(): float
    {
        $totalPoints = $this->quiz->getTotalPoints();
        if ($totalPoints === 0) {
            return 0;
        }

        $earnedPoints = $this->answers()->sum('points_awarded');
        $score = round(($earnedPoints / $totalPoints) * 100, 2);

        // Update score and is_passed
        $this->score = $score;
        $this->is_passed = $score >= $this->quiz->passing_score;
        $this->save();

        return $score;
    }

    /**
     * Auto-grade all auto-gradable answers.
     *
     * @return void
     */
    public function autoGradeAnswers(): void
    {
        $answers = $this->answers()->with('question')->get();

        foreach ($answers as $answer) {
            $question = $answer->question;

            // Only auto-grade MULTIPLE_CHOICE and TRUE_FALSE
            if ($question->isAutoGradable()) {
                $isCorrect = $question->checkAnswer($answer->answer_text);

                $answer->is_correct = $isCorrect;
                $answer->points_awarded = $isCorrect ? $question->points : 0;
                $answer->save();
            }
        }
    }

    /**
     * Submit the quiz attempt.
     *
     * @return bool
     */
    public function submit(): bool
    {
        if ($this->isCompleted()) {
            return false; // Already submitted
        }

        // Calculate time spent
        $this->time_spent = now()->diffInSeconds($this->started_at);

        // Set submitted time
        $this->submitted_at = now();

        // Auto-grade all answers
        $this->autoGradeAnswers();

        // Calculate final score
        $this->calculateScore();

        return true;
    }

    /**
     * Get the answer for a specific question.
     *
     * @param \App\Models\Question $question
     * @return \App\Models\Answer|null
     */
    public function getAnswerForQuestion(Question $question): ?Answer
    {
        return $this->answers()->where('question_id', $question->id)->first();
    }

    /**
     * Check if all questions have been answered.
     *
     * @return bool
     */
    public function isComplete(): bool
    {
        $totalQuestions = $this->quiz->questions()->count();
        $answeredQuestions = $this->answers()->count();

        return $totalQuestions === $answeredQuestions;
    }

    /**
     * Get progress percentage.
     *
     * @return int
     */
    public function getProgressPercentage(): int
    {
        $totalQuestions = $this->quiz->questions()->count();
        if ($totalQuestions === 0) {
            return 0;
        }

        $answeredQuestions = $this->answers()->count();
        return (int) round(($answeredQuestions / $totalQuestions) * 100);
    }

    /**
     * Get the number of correct answers.
     *
     * @return int
     */
    public function getCorrectAnswersCount(): int
    {
        return $this->answers()
            ->where('is_correct', true)
            ->count();
    }

    /**
     * Get the number of incorrect answers.
     *
     * @return int
     */
    public function getIncorrectAnswersCount(): int
    {
        return $this->answers()
            ->where('is_correct', false)
            ->count();
    }

    /**
     * Get the number of unanswered questions.
     *
     * @return int
     */
    public function getUnansweredCount(): int
    {
        $totalQuestions = $this->quiz->questions()->count();
        $answeredQuestions = $this->answers()->count();

        return $totalQuestions - $answeredQuestions;
    }

    /**
     * Get attempt summary for display.
     *
     * @return array
     */
    public function getSummary(): array
    {
        return [
            'id' => $this->id,
            'attempt_number' => $this->attempt_number,
            'score' => $this->score,
            'is_passed' => $this->is_passed,
            'time_spent' => $this->time_spent,
            'started_at' => $this->started_at?->toDateTimeString(),
            'submitted_at' => $this->submitted_at?->toDateTimeString(),
            'correct_answers' => $this->getCorrectAnswersCount(),
            'incorrect_answers' => $this->getIncorrectAnswersCount(),
            'unanswered' => $this->getUnansweredCount(),
            'total_questions' => $this->quiz->questions()->count(),
            'passing_score' => $this->quiz->passing_score,
            'progress_percentage' => $this->getProgressPercentage(),
        ];
    }
}
