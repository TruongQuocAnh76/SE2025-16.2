<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Quiz extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'quiz_type',
        'time_limit',
        'passing_score',
        'max_attempts',
        'order_index',
        'is_active',
        'course_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'time_limit' => 'integer',
        'passing_score' => 'integer',
        'max_attempts' => 'integer',
        'order_index' => 'integer',
    ];

    /**
     * Get the course that owns the quiz.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the questions for the quiz.
     */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('order_index');
    }

    /**
     * Get the quiz attempts for the quiz.
     */
    public function quizAttempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    /**
     * Calculate total points for this quiz.
     *
     * @return int
     */
    public function getTotalPoints(): int
    {
        return $this->questions()->sum('points');
    }

    /**
     * Check if quiz is available for a student.
     *
     * @param \App\Models\User $student
     * @return bool
     */
    public function isAvailableForStudent(User $student): bool
    {
        // Check if quiz is active
        if (!$this->is_active) {
            return false;
        }

        // Check if student is enrolled in the course
        $enrollment = Enrollment::where('student_id', $student->id)
            ->where('course_id', $this->course_id)
            ->where('status', 'ACTIVE')
            ->first();

        if (!$enrollment) {
            return false;
        }

        // Check if student has remaining attempts
        if ($this->max_attempts !== null) {
            $attemptsCount = $this->quizAttempts()
                ->where('student_id', $student->id)
                ->count();

            if ($attemptsCount >= $this->max_attempts) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get student's attempts for this quiz.
     *
     * @param \App\Models\User $student
     * @return \Illuminate\Support\Collection
     */
    public function getStudentAttempts(User $student): Collection
    {
        return $this->quizAttempts()
            ->where('student_id', $student->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get student's remaining attempts count.
     *
     * @param \App\Models\User $student
     * @return int|null Returns null if unlimited attempts
     */
    public function getRemainingAttempts(User $student): ?int
    {
        if ($this->max_attempts === null) {
            return null; // Unlimited attempts
        }

        $usedAttempts = $this->quizAttempts()
            ->where('student_id', $student->id)
            ->count();

        return max(0, $this->max_attempts - $usedAttempts);
    }

    /**
     * Check if student has passed this quiz.
     *
     * @param \App\Models\User $student
     * @return bool
     */
    public function hasStudentPassed(User $student): bool
    {
        return $this->quizAttempts()
            ->where('student_id', $student->id)
            ->where('is_passed', true)
            ->exists();
    }

    /**
     * Get student's best score for this quiz.
     *
     * @param \App\Models\User $student
     * @return float|null
     */
    public function getStudentBestScore(User $student): ?float
    {
        return $this->quizAttempts()
            ->where('student_id', $student->id)
            ->whereNotNull('score')
            ->max('score');
    }
}
