<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Answer extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'answer_text',
        'is_correct',
        'points_awarded',
        'attempt_id',
        'question_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_correct' => 'boolean',
        'points_awarded' => 'integer',
    ];

    /**
     * Get the quiz attempt that owns the answer.
     */
    public function attempt(): BelongsTo
    {
        return $this->belongsTo(QuizAttempt::class, 'attempt_id');
    }

    /**
     * Get the question for this answer.
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Auto-grade this answer.
     *
     * @return bool
     */
    public function autoGrade(): bool
    {
        if (!$this->question->isAutoGradable()) {
            return false; // Cannot auto-grade
        }

        $isCorrect = $this->question->checkAnswer($this->answer_text);
        $this->is_correct = $isCorrect;
        $this->points_awarded = $isCorrect ? $this->question->points : 0;

        return $this->save();
    }

    /**
     * Manually grade this answer.
     *
     * @param int $pointsAwarded
     * @param bool|null $isCorrect
     * @return bool
     */
    public function manualGrade(int $pointsAwarded, ?bool $isCorrect = null): bool
    {
        // Validate points don't exceed question's max points
        $maxPoints = $this->question->points;
        $pointsAwarded = min($pointsAwarded, $maxPoints);

        $this->points_awarded = $pointsAwarded;

        if ($isCorrect !== null) {
            $this->is_correct = $isCorrect;
        } else {
            // Determine correctness based on points awarded
            $this->is_correct = $pointsAwarded > 0;
        }

        return $this->save();
    }

    /**
     * Check if this answer is fully graded.
     *
     * @return bool
     */
    public function isGraded(): bool
    {
        return $this->is_correct !== null;
    }

    /**
     * Check if this answer needs manual grading.
     *
     * @return bool
     */
    public function needsManualGrading(): bool
    {
        // If already graded, no need for manual grading
        if ($this->isGraded()) {
            return false;
        }

        // Check if question type requires manual grading
        return !$this->question->isAutoGradable();
    }

    /**
     * Get the percentage score for this answer.
     *
     * @return float
     */
    public function getScorePercentage(): float
    {
        if ($this->question->points === 0) {
            return 0;
        }

        return round(($this->points_awarded / $this->question->points) * 100, 2);
    }

    /**
     * Get formatted answer text for display.
     *
     * @return string
     */
    public function getFormattedAnswer(): string
    {
        if ($this->question->question_type === 'MULTIPLE_CHOICE' && is_array($this->question->options)) {
            // If answer_text is an index
            if (is_numeric($this->answer_text) && isset($this->question->options[$this->answer_text])) {
                return $this->question->options[$this->answer_text];
            }
        }

        return $this->answer_text;
    }

    /**
     * Get the student who submitted this answer.
     *
     * @return \App\Models\User
     */
    public function getStudent(): User
    {
        return $this->attempt->student;
    }

    /**
     * Get formatted answer for display (after quiz submission).
     *
     * @return array
     */
    public function toDisplayArray(): array
    {
        return [
            'id' => $this->id,
            'question_id' => $this->question_id,
            'question_text' => $this->question->question_text,
            'question_type' => $this->question->question_type,
            'student_answer' => $this->getFormattedAnswer(),
            'is_correct' => $this->is_correct,
            'points_awarded' => $this->points_awarded,
            'max_points' => $this->question->points,
            'score_percentage' => $this->getScorePercentage(),
            'correct_answer' => $this->question->getCorrectAnswerForDisplay(),
            'explanation' => $this->question->explanation,
        ];
    }

    /**
     * Get answer summary (without showing correct answer - for in-progress attempts).
     *
     * @return array
     */
    public function toSummaryArray(): array
    {
        return [
            'id' => $this->id,
            'question_id' => $this->question_id,
            'student_answer' => $this->getFormattedAnswer(),
            'is_graded' => $this->isGraded(),
        ];
    }
}
