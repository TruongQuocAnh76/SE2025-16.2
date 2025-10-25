<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'question_text',
        'question_type',
        'points',
        'order_index',
        'options',
        'correct_answer',
        'explanation',
        'quiz_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'options' => 'array',
        'points' => 'integer',
        'order_index' => 'integer',
    ];

    /**
     * Get the quiz that owns the question.
     */
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    /**
     * Get the answers for the question.
     */
    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }

    /**
     * Check if the given answer is correct.
     *
     * @param string $userAnswer
     * @return bool
     */
    public function checkAnswer(string $userAnswer): bool
    {
        if (!$this->isAutoGradable()) {
            return false; // Manual grading required
        }

        $correctAnswer = trim(strtolower($this->correct_answer));
        $userAnswer = trim(strtolower($userAnswer));

        return $correctAnswer === $userAnswer;
    }

    /**
     * Check if this question can be auto-graded.
     *
     * @return bool
     */
    public function isAutoGradable(): bool
    {
        // Only MULTIPLE_CHOICE and TRUE_FALSE can be auto-graded
        // SHORT_ANSWER and ESSAY need manual grading
        return in_array($this->question_type, ['MULTIPLE_CHOICE', 'TRUE_FALSE']);
    }

    /**
     * Get points awarded for a student's answer.
     *
     * @param string $userAnswer
     * @return int
     */
    public function getPointsForAnswer(string $userAnswer): int
    {
        if (!$this->isAutoGradable()) {
            return 0; // Needs manual grading
        }

        return $this->checkAnswer($userAnswer) ? $this->points : 0;
    }

    /**
     * Get the correct answer for display (after quiz completion).
     *
     * @return string
     */
    public function getCorrectAnswerForDisplay(): string
    {
        if ($this->question_type === 'MULTIPLE_CHOICE' && is_array($this->options)) {
            // If correct_answer is an index
            if (is_numeric($this->correct_answer) && isset($this->options[$this->correct_answer])) {
                return $this->options[$this->correct_answer];
            }
        }

        return $this->correct_answer;
    }

    /**
     * Get formatted options for display.
     *
     * @return array
     */
    public function getFormattedOptions(): array
    {
        if (!is_array($this->options)) {
            return [];
        }

        $formatted = [];
        foreach ($this->options as $index => $option) {
            $formatted[] = [
                'value' => (string) $index,
                'text' => $option,
            ];
        }

        return $formatted;
    }

    /**
     * Format question for student view (hide correct answer).
     *
     * @return array
     */
    public function toStudentArray(): array
    {
        return [
            'id' => $this->id,
            'question_text' => $this->question_text,
            'question_type' => $this->question_type,
            'points' => $this->points,
            'order_index' => $this->order_index,
            'options' => $this->getFormattedOptions(),
            // Do NOT include 'correct_answer' for students during quiz
        ];
    }

    /**
     * Format question for review (after submission - show correct answer).
     *
     * @return array
     */
    public function toReviewArray(): array
    {
        return [
            'id' => $this->id,
            'question_text' => $this->question_text,
            'question_type' => $this->question_type,
            'points' => $this->points,
            'options' => $this->getFormattedOptions(),
            'correct_answer' => $this->getCorrectAnswerForDisplay(),
            'explanation' => $this->explanation,
        ];
    }
}
