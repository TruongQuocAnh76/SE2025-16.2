<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\LearningController;
use App\Http\Controllers\GradingController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\CertificateVerificationController;
use App\Http\Controllers\SystemController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\TeacherApplicationController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\AdminController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::get('/', fn() => response()->json(['message' => 'CertChain API v1 is running']));

/* ========================
 * PREVIEW ENDPOINTS (for development/testing)
 * ======================== */
Route::get('/_preview/certificates', [CertificateController::class, 'preview']);

/* ========================
 * AUTHENTICATION
 * ======================== */
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout']);
    // Forgot password & reset password (chuyển về AuthController)
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
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

// Protected course management routes (require authentication)
Route::middleware('auth:sanctum')->prefix('courses')->group(function () {
    Route::post('/', [CourseController::class, 'store']); // Teacher/Admin create course (supports modules)
    Route::post('/videos/{lessonId}/upload-complete', [CourseController::class, 'notifyVideoUploadComplete']); // Notify video upload completion
    Route::get('/lesson/{lessonId}/hls-status', [CourseController::class, 'checkHlsProcessingStatus']); // Check HLS processing status
    Route::get('/{id}/modules', [CourseController::class, 'getModulesWithLessons']);
    Route::get('/{id}/students', [CourseController::class, 'getEnrolledStudents']); // Teacher only
    Route::get('/{id}/enrollment/check', [LessonController::class, 'checkEnrollment']); // Check enrollment
    Route::put('/{id}', [CourseController::class, 'update']); // Teacher/Admin update
    Route::delete('/{id}', [CourseController::class, 'destroy']); // Teacher/Admin delete
    Route::post('/{id}/enroll', [CourseController::class, 'enroll']); // Student enroll
    Route::post('/{id}/review', [CourseController::class, 'addReview']); // Add review
});

/* ========================
 * LESSONS
 * ======================== */
Route::middleware('auth:sanctum')->prefix('lessons')->group(function () {
    Route::get('/{lessonId}', [LessonController::class, 'show']); // Get lesson details
});

Route::middleware('auth:sanctum')->prefix('modules')->group(function () {
    Route::get('/{moduleId}/lessons', [LessonController::class, 'getByModule']); // Get lessons by module
});

/* ========================
 * LEARNING PROGRESS
 * ======================== */
Route::middleware('auth:sanctum')->prefix('learning')->group(function () {
    Route::get('/course/{courseId}', [LearningController::class, 'getCourseProgress']);
    Route::get('/lesson/{lessonId}/progress', [LessonController::class, 'getProgress']); // Get lesson progress
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
 * TEACHER APPLICATIONS
 * ======================== */
Route::middleware('auth:sanctum')->prefix('teacher-applications')->group(function () {
    Route::post('/submit', [TeacherApplicationController::class, 'submit']); // Submit application
    Route::get('/my-applications', [TeacherApplicationController::class, 'myApplications']); // Get my applications
    Route::get('/', [TeacherApplicationController::class, 'index']); // Get all applications (Admin)
    Route::get('/{id}', [TeacherApplicationController::class, 'show']); // Get application details
    Route::post('/{id}/approve', [TeacherApplicationController::class, 'approve']); // Approve (Admin)
    Route::post('/{id}/reject', [TeacherApplicationController::class, 'reject']); // Reject (Admin)
});

/* ========================
 * TEACHERS
 * ======================== */
Route::middleware('auth:sanctum')->prefix('teachers')->group(function () {
    Route::get('/{id}/courses', [TeacherController::class, 'getCourses']); // Get teacher's courses
    Route::get('/{id}/students', [TeacherController::class, 'getStudents']); // Get students in teacher's courses
    Route::get('/{id}/statistics', [TeacherController::class, 'getStatistics']); // Get teacher statistics
});

// Public certificate verification routes (no auth required)
Route::prefix('certificates')->group(function () {
    Route::post('/verify', [CertificateVerificationController::class, 'verify']); // Blockchain verify
});

// Authenticated certificate verification routes
Route::middleware('auth:sanctum')->prefix('certificates')->group(function () {
    Route::get('/{certificate_number}/blockchain-status', [CertificateVerificationController::class, 'getBlockchainStatus']);
    Route::post('/{certificate_number}/retry-blockchain', [CertificateVerificationController::class, 'retryBlockchainIssuance']);
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
 * ADMIN DASHBOARD
 * ======================== */
Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
    Route::get('/dashboard-stats', [AdminController::class, 'getDashboardStats']);
    Route::get('/teacher-applications', [AdminController::class, 'getPendingTeacherApplications']);
    Route::get('/course-applications', [AdminController::class, 'getPendingCourseApplications']);
    Route::get('/certificates-overview', [AdminController::class, 'getCertificatesOverview']);
    Route::get('/recent-certificates', [AdminController::class, 'getRecentCertificates']);
    Route::get('/system-logs', [AdminController::class, 'getSystemLogs']);
    Route::post('/teacher-applications/{applicationId}/approve', [AdminController::class, 'approveTeacher']);
    Route::post('/teacher-applications/{applicationId}/reject', [AdminController::class, 'rejectTeacher']);
    Route::post('/courses/{courseId}/approve', [AdminController::class, 'approveCourse']);
    Route::post('/courses/{courseId}/reject', [AdminController::class, 'rejectCourse']);
    
    // Admin list endpoints
    Route::get('/list/users', [AdminController::class, 'listUsers']);
    Route::get('/list/courses', [AdminController::class, 'listCourses']);
    Route::get('/list/certificates', [AdminController::class, 'listCertificates']);
    Route::get('/list/applications', [AdminController::class, 'listApplications']);
    Route::get('/list/logs', [AdminController::class, 'listLogs']);
});

/* ========================
 * TAGS (Public)
 * ======================== */
Route::prefix('tags')->group(function () {
    Route::get('/', [\App\Http\Controllers\TagController::class, 'index']); // Lấy tất cả tags
    
    // Route tạo tag mới (cần authentication)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/', [\App\Http\Controllers\TagController::class, 'store']); // Tạo tag mới
    });
});

/* ========================
 * PAYMENTS
 * ======================== */
Route::middleware('auth:sanctum')->prefix('payments')->group(function () {
    Route::post('/create', [PaymentController::class, 'createPayment']); // Create payment intent
    Route::get('/', [PaymentController::class, 'index']); // Get payment history
    Route::get('/{id}', [PaymentController::class, 'show']); // Get payment details
    
    // Stripe routes
    Route::post('/{id}/stripe/create-intent', [PaymentController::class, 'createStripeIntent']); // Create Stripe payment intent
    Route::post('/{id}/stripe/complete', [PaymentController::class, 'completeStripePayment']); // Complete Stripe payment
});

// Stripe Webhook (no auth needed)
Route::post('/stripe/webhook', [PaymentController::class, 'stripeWebhook']);
