<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\LearningController;
use App\Http\Controllers\GradingController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\SystemController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::get('/', fn() => response()->json(['message' => 'CertChain API v1 is running']));

/* ========================
 * AUTHENTICATION
 * ======================== */
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

// OAuth Routes - Need session middleware for Socialite
Route::middleware(['web'])->prefix('auth')->group(function () {
    Route::get('/google', [AuthController::class, 'redirectToGoogle']);
    Route::get('/google/callback', [AuthController::class, 'handleGoogleCallback']);
    Route::get('/facebook', [AuthController::class, 'redirectToFacebook']);
    Route::get('/facebook/callback', [AuthController::class, 'handleFacebookCallback']);
});

Route::middleware('auth:sanctum')->prefix('auth')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
});

/* ========================
 * USER MANAGEMENT
 * ======================== */
Route::middleware('auth:sanctum')->prefix('users')->group(function () {
    Route::get('/', [UserController::class, 'index']); // Admin only
    Route::get('/filter/by-role', [UserController::class, 'filterByRole']); // MUST be before /{id}
    Route::get('/{id}', [UserController::class, 'show']);
    Route::put('/{id}', [UserController::class, 'update']);
    Route::delete('/{id}', [UserController::class, 'destroy']); // Admin only
    Route::get('/{id}/reviews', [UserController::class, 'getUserReviews']);
    Route::get('/{id}/certificates', [UserController::class, 'getUserCertificates']);
    Route::get('/{id}/enrollments', [UserController::class, 'getUserEnrollments']);
    Route::get('/{id}/quiz-attempts-count', [UserController::class, 'getUserQuizAttemptsCount']);
});

/* ========================
 * COURSE MANAGEMENT
 * ======================== */
Route::prefix('courses')->group(function () {
    Route::get('/', [CourseController::class, 'index']); // List all courses
    Route::get('/search', [CourseController::class, 'search']); // Search courses by name
    Route::get('/{id}', [CourseController::class, 'show']); // Get course details
});
// --- NHÓM RIÊNG TƯ (PRIVATE) ---
// Phải đăng nhập (auth:sanctum) để thực hiện các hành động này
Route::middleware('auth:sanctum')->prefix('courses')->group(function () {
    Route::post('/', [CourseController::class, 'store']); // Teacher/Admin create
    Route::get('/{id}/modules', [CourseController::class, 'getModulesWithLessons']);
    Route::get('/{id}/students', [CourseController::class, 'getEnrolledStudents']); // Teacher only
    Route::put('/{id}', [CourseController::class, 'update']); // Teacher/Admin update
    Route::delete('/{id}', [CourseController::class, 'destroy']); // Teacher/Admin delete
    Route::post('/{id}/enroll', [CourseController::class, 'enroll']); // Student enroll
    Route::post('/{id}/review', [CourseController::class, 'addReview']); // Add review
});

/* ========================
 * LEARNING PROGRESS
 * ======================== */
Route::middleware('auth:sanctum')->prefix('learning')->group(function () {
    Route::get('/course/{courseId}', [LearningController::class, 'getCourseProgress']);
    Route::post('/lesson/{lessonId}/complete', [LearningController::class, 'markLessonCompleted']);
    Route::post('/lesson/{lessonId}/time', [LearningController::class, 'updateTimeSpent']);
    Route::post('/course/{courseId}/complete', [LearningController::class, 'completeCourse']);
    Route::get('/student/{studentId}/courses/time-spent', [LearningController::class, 'getCoursesTimeSpent']);
    Route::get('/student/{studentId}/courses/progress', [LearningController::class, 'getCoursesProgress']);
    Route::get('/courses/progress', [LearningController::class, 'getMyCoursesProgress']);
});

/* ========================
 * QUIZZES
 * ======================== */
Route::middleware('auth:sanctum')->prefix('quizzes')->group(function () {
    Route::get('/course/{courseId}', [QuizController::class, 'index']); // List quizzes in course
    Route::post('/course/{courseId}', [QuizController::class, 'store']); // Teacher create quiz
    Route::get('/{quizId}', [QuizController::class, 'show']); // Get quiz with questions
    Route::put('/{quizId}', [QuizController::class, 'update']); // Teacher update quiz
    Route::delete('/{quizId}', [QuizController::class, 'destroy']); // Teacher delete quiz
    Route::post('/{quizId}/start', [QuizController::class, 'startAttempt']); // Student start attempt
    Route::post('/attempt/{attemptId}/submit', [QuizController::class, 'submitAttempt']); // Student submit
    Route::get('/{quizId}/attempts', [QuizController::class, 'attemptsHistory']); // Get attempt history
    Route::get('/{quizId}/stats', [QuizController::class, 'getStudentStats']); // Get student stats
    Route::get('/{quizId}/student-attempts', [QuizController::class, 'getStudentAttempts']); // Get student attempts

    // Question management
    Route::get('/{quizId}/questions', [QuestionController::class, 'index']); // List questions in quiz
    Route::post('/{quizId}/questions', [QuestionController::class, 'store']); // Teacher create question
});

/* ========================
 * QUESTIONS
 * ======================== */
Route::middleware('auth:sanctum')->prefix('questions')->group(function () {
    Route::get('/{questionId}', [QuestionController::class, 'show']); // Get question details
    Route::put('/{questionId}', [QuestionController::class, 'update']); // Teacher update question
    Route::delete('/{questionId}', [QuestionController::class, 'destroy']); // Teacher delete question
});

/* ========================
 * GRADING
 * ======================== */
Route::middleware('auth:sanctum')->prefix('grading')->group(function () {
    Route::post('/attempts/{attemptId}/auto-grade', [GradingController::class, 'autoGradeAttempt']); // Auto-grade attempt
    Route::post('/answers/{answerId}/manual-grade', [GradingController::class, 'manualGradeAnswer']); // Manual grade answer
    Route::get('/attempts/{attemptId}/pending', [GradingController::class, 'getPendingAnswers']); // Get pending answers
    Route::get('/attempts/{attemptId}/review', [GradingController::class, 'getAttemptReview']); // Get attempt review
});

/* ========================
 * CERTIFICATES
 * ======================== */
Route::middleware('auth:sanctum')->prefix('certificates')->group(function () {
    Route::get('/mine', [CertificateController::class, 'myCertificates']); // My certificates
    Route::get('/verify/{certificateNumber}', [CertificateController::class, 'verifyCertificate']); // Public verify
    Route::post('/issue/{courseId}', [CertificateController::class, 'issueCertificate']); // Issue cert
    Route::get('/{certificateId}', [CertificateController::class, 'show']); // Get cert details
    Route::post('/{certificateId}/revoke', [CertificateController::class, 'revoke']); // Revoke (Teacher/Admin)
    Route::post('/{certificateId}/attach-blockchain', [CertificateController::class, 'attachBlockchainData']); // Attach blockchain
});

/* ========================
 * SYSTEM / ADMIN TOOLS
 * ======================== */
Route::middleware('auth:sanctum')->prefix('system')->group(function () {
    Route::post('/log', [SystemController::class, 'logAction']); // Log action
    Route::get('/logs', [SystemController::class, 'getLogs']); // Get logs (Admin)
    Route::get('/logs/{id}', [SystemController::class, 'showLog']); // Get log detail
    Route::delete('/logs/clear', [SystemController::class, 'clearOldLogs']); // Delete old logs (Admin)
    Route::get('/cache', [SystemController::class, 'cacheStatus']); // Cache status
    Route::delete('/cache', [SystemController::class, 'clearCache']); // Clear cache (Admin)
    Route::get('/jobs', [SystemController::class, 'getJobs']); // Get jobs (Admin)
    Route::delete('/jobs/{id}', [SystemController::class, 'deleteJob']); // Delete job (Admin)
    Route::post('/jobs/{id}/retry', [SystemController::class, 'retryJob']); // Retry job (Admin)
});

/* ========================
 * TAGS (Public)
 * ======================== */
Route::prefix('tags')->group(function () {
    Route::get('/', [\App\Http\Controllers\TagController::class, 'index']); // Lấy tất cả tags
});
