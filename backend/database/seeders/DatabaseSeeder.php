<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CourseSeeder::class,
            ModuleSeeder::class,
            LessonSeeder::class,
            EnrollmentSeeder::class,
            ProgressSeeder::class,
            QuizSeeder::class,
            QuestionSeeder::class,
            QuizAttemptSeeder::class,
            AnswerSeeder::class,
            CertificateSeeder::class,
            BlockchainTransactionSeeder::class,
            ReviewSeeder::class,
            SystemLogSeeder::class,
        ]);
    }
}
