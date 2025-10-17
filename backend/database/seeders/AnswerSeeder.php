<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AnswerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get quiz attempts
        $aliceVueAttempt = DB::table('quiz_attempts')
            ->join('users', 'quiz_attempts.student_id', '=', 'users.id')
            ->join('quizzes', 'quiz_attempts.quiz_id', '=', 'quizzes.id')
            ->where('users.email', 'alice.student@example.com')
            ->where('quizzes.title', 'Vue.js Fundamentals Quiz')
            ->select('quiz_attempts.*')
            ->first();

        $bobWebAttempt1 = DB::table('quiz_attempts')
            ->join('users', 'quiz_attempts.student_id', '=', 'users.id')
            ->join('quizzes', 'quiz_attempts.quiz_id', '=', 'quizzes.id')
            ->where('users.email', 'bob.learner@example.com')
            ->where('quizzes.title', 'Web Development Knowledge Check')
            ->where('quiz_attempts.attempt_number', 1)
            ->select('quiz_attempts.*')
            ->first();

        // Get questions
        $vueQuestions = DB::table('questions')
            ->join('quizzes', 'questions.quiz_id', '=', 'quizzes.id')
            ->where('quizzes.title', 'Vue.js Fundamentals Quiz')
            ->select('questions.*')
            ->orderBy('questions.order_index')
            ->get();

        $webQuestions = DB::table('questions')
            ->join('quizzes', 'questions.quiz_id', '=', 'quizzes.id')
            ->where('quizzes.title', 'Web Development Knowledge Check')
            ->select('questions.*')
            ->orderBy('questions.order_index')
            ->get();

        $answers = [];

        // Alice's Vue.js Quiz Answers (she scored 85%)
        if ($aliceVueAttempt && $vueQuestions->count() > 0) {
            $answers[] = [
                'id' => Str::uuid(),
                'answer_text' => 'A JavaScript framework for building user interfaces',
                'is_correct' => true,
                'points_awarded' => 1,
                'attempt_id' => $aliceVueAttempt->id,
                'question_id' => $vueQuestions[0]->id,
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(5),
            ];

            $answers[] = [
                'id' => Str::uuid(),
                'answer_text' => 'v-model',
                'is_correct' => true,
                'points_awarded' => 1,
                'attempt_id' => $aliceVueAttempt->id,
                'question_id' => $vueQuestions[1]->id,
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(5),
            ];

            $answers[] = [
                'id' => Str::uuid(),
                'answer_text' => 'True',
                'is_correct' => true,
                'points_awarded' => 1,
                'attempt_id' => $aliceVueAttempt->id,
                'question_id' => $vueQuestions[2]->id,
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(5),
            ];

            $answers[] = [
                'id' => Str::uuid(),
                'answer_text' => 'Reactivity in Vue.js means that when data changes, the view automatically updates. Vue tracks dependencies and re-renders when reactive data changes.',
                'is_correct' => true,
                'points_awarded' => 2, // Partial credit for short answer
                'attempt_id' => $aliceVueAttempt->id,
                'question_id' => $vueQuestions[3]->id,
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(5),
            ];
        }

        // Bob's Web Development Quiz Answers (first attempt - 65%)
        if ($bobWebAttempt1 && $webQuestions->count() > 0) {
            $answers[] = [
                'id' => Str::uuid(),
                'answer_text' => 'HyperText Markup Language',
                'is_correct' => true,
                'points_awarded' => 1,
                'attempt_id' => $bobWebAttempt1->id,
                'question_id' => $webQuestions[0]->id,
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subDays(3),
            ];

            $answers[] = [
                'id' => Str::uuid(),
                'answer_text' => 'False', // Wrong answer
                'is_correct' => false,
                'points_awarded' => 0,
                'attempt_id' => $bobWebAttempt1->id,
                'question_id' => $webQuestions[1]->id,
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subDays(3),
            ];

            $answers[] = [
                'id' => Str::uuid(),
                'answer_text' => 'Float',
                'is_correct' => true,
                'points_awarded' => 1,
                'attempt_id' => $bobWebAttempt1->id,
                'question_id' => $webQuestions[2]->id,
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subDays(3),
            ];
        }

        if (!empty($answers)) {
            DB::table('answers')->insert($answers);
        }
    }
}
