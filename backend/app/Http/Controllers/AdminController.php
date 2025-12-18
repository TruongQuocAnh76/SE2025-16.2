<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Course;
use App\Models\Certificate;
use App\Models\TeacherApplication;
use App\Models\SystemLog;
use App\Services\TeacherApplicationService;
use App\Services\SystemLogService;
use Exception;

class AdminController extends Controller
{
    protected $teacherApplicationService;
    protected $systemLogService;

    public function __construct(TeacherApplicationService $teacherApplicationService, SystemLogService $systemLogService)
    {
        $this->teacherApplicationService = $teacherApplicationService;
        $this->systemLogService = $systemLogService;
    }

    /**
     * Create a system log entry
     */
    private function logAction(Request $request, string $level, string $message, array $context = []): void
    {
        $this->systemLogService->logAction($level, $message, Auth::id(), $context, $request->ip(), $request->userAgent());
    }

    /**
     * Get admin dashboard statistics
     */
    public function getDashboardStats()
    {
        $user = Auth::user();
        
        if ($user->role !== 'ADMIN') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $totalUsers = User::count();
        $totalCourses = Course::count();
        $certificatesIssued = Certificate::where('status', 'ISSUED')->count();
        
        // Pending actions = pending teacher applications + pending courses
        $pendingTeacherApps = TeacherApplication::where('status', 'PENDING')->count();
        $pendingCourses = Course::where('status', 'PENDING')->count();
        $pendingActions = $pendingTeacherApps + $pendingCourses;

        return response()->json([
            'total_users' => $totalUsers,
            'total_courses' => $totalCourses,
            'certificates_issued' => $certificatesIssued,
            'pending_actions' => $pendingActions
        ]);
    }

    /**
     * Get pending teacher applications
     */
    public function getPendingTeacherApplications()
    {
        $user = Auth::user();
        
        if ($user->role !== 'ADMIN') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $applications = TeacherApplication::with('user')
            ->where('status', 'PENDING')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($app) {
                return [
                    'id' => $app->id,
                    'user_id' => $app->user_id,
                    'user_name' => $app->user ? ($app->user->first_name . ' ' . $app->user->last_name) : 'Unknown',
                    'first_name' => $app->user ? $app->user->first_name : '',
                    'last_name' => $app->user ? $app->user->last_name : '',
                    'user_email' => $app->user ? $app->user->email : '',
                    'username' => $app->user ? $app->user->username : '',
                    'avatar' => $app->user ? $app->user->avatar : null,
                    'bio' => $app->user ? $app->user->bio : null,
                    'current_role' => $app->user ? $app->user->role : 'STUDENT',
                    'requested_role' => 'TEACHER',
                    'certificate_title' => $app->certificate_title,
                    'issuer' => $app->issuer,
                    'issue_date' => $app->issue_date ? $app->issue_date->format('Y-m-d') : null,
                    'expiry_date' => $app->expiry_date ? $app->expiry_date->format('Y-m-d') : null,
                    'submitted_at' => $app->created_at->format('Y-m-d'),
                ];
            });

        return response()->json($applications);
    }

    /**
     * Get pending course applications (courses awaiting approval)
     */
    public function getPendingCourseApplications()
    {
        $user = Auth::user();
        
        if ($user->role !== 'ADMIN') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $courses = Course::with('teacher')
            ->where('status', 'PENDING')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($course) {
                return [
                    'id' => $course->id,
                    'title' => $course->title,
                    'teacher_name' => $course->teacher ? ($course->teacher->first_name . ' ' . $course->teacher->last_name) : 'Unknown',
                    'category' => $course->category ?? 'General',
                    'level' => $course->level ?? 'BEGINNER',
                    'duration' => $course->duration ?? 0,
                    'submitted_at' => $course->created_at->format('Y-m-d'),
                ];
            });

        return response()->json($courses);
    }

    /**
     * Get certificates overview stats
     */
    public function getCertificatesOverview()
    {
        $user = Auth::user();
        
        if ($user->role !== 'ADMIN') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $issued = Certificate::where('status', 'ISSUED')->count();
        $pending = Certificate::where('status', 'PENDING')->count();
        
        // For "verified", count certificates that have blockchain transactions confirmed
        $verified = Certificate::whereHas('blockchainTransaction', function ($query) {
            $query->where('status', 'CONFIRMED');
        })->count();

        return response()->json([
            'issued' => $issued,
            'verified' => $verified,
            'pending' => $pending
        ]);
    }

    /**
     * Get recent certificates
     */
    public function getRecentCertificates()
    {
        $user = Auth::user();
        
        if ($user->role !== 'ADMIN') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $certificates = Certificate::with(['student', 'course', 'course.teacher'])
            ->where('status', 'ISSUED')
            ->orderBy('issued_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($cert) {
                return [
                    'id' => $cert->id,
                    'certificate_number' => $cert->certificate_number,
                    'student_name' => $cert->student ? ($cert->student->first_name . ' ' . $cert->student->last_name) : 'Unknown',
                    'student_avatar' => $cert->student->avatar ?? null,
                    'course_name' => $cert->course ? $cert->course->title : 'Unknown Course',
                    'instructor_name' => $cert->course && $cert->course->teacher 
                        ? ($cert->course->teacher->first_name . ' ' . $cert->course->teacher->last_name) 
                        : 'Unknown',
                    'issued_at' => $cert->issued_at ? $cert->issued_at->format('Y-m-d') : null,
                    'is_verified' => $cert->blockchainTransaction && $cert->blockchainTransaction->status === 'CONFIRMED',
                ];
            });

        return response()->json($certificates);
    }

    /**
     * Get system logs for admin dashboard
     */
    public function getSystemLogs()
    {
        $user = Auth::user();
        
        if ($user->role !== 'ADMIN') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $logs = SystemLog::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'level' => $log->level,
                    'message' => $log->message,
                    'context' => $log->context,
                    'action_by' => $log->user ? ($log->user->first_name . ' ' . $log->user->last_name) : 'System',
                    'timestamp' => $log->created_at->format('H:i:s - d/m/Y'),
                ];
            });

        return response()->json($logs);
    }

    /**
     * Approve a teacher application
     */
    public function approveTeacher(Request $request, $applicationId)
    {
        $user = Auth::user();
        
        if ($user->role !== 'ADMIN') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $application = $this->teacherApplicationService->approveApplication($applicationId, $user->id);
            
            // Log the action
            $this->logAction($request, 'INFO', 'Teacher Application Approved', [
                'application_id' => $applicationId,
                'applicant_user_id' => $application->user_id,
                'applicant_name' => $application->user ? ($application->user->first_name . ' ' . $application->user->last_name) : 'Unknown',
                'certificate_title' => $application->certificate_title,
                'issuer' => $application->issuer,
            ]);

            return response()->json([
                'message' => 'Teacher application approved successfully',
                'application' => $application
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to approve application',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Reject a teacher application
     */
    public function rejectTeacher(Request $request, $applicationId)
    {
        $user = Auth::user();
        
        if ($user->role !== 'ADMIN') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $reason = $request->input('reason', 'No reason provided');

        try {
            $application = $this->teacherApplicationService->rejectApplication($applicationId, $user->id, $reason);
            
            // Log the action
            $this->logAction($request, 'INFO', 'Teacher Application Rejected', [
                'application_id' => $applicationId,
                'applicant_user_id' => $application->user_id,
                'applicant_name' => $application->user ? ($application->user->first_name . ' ' . $application->user->last_name) : 'Unknown',
                'reason' => $reason,
            ]);

            return response()->json([
                'message' => 'Teacher application rejected',
                'application' => $application
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to reject application',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Approve a course
     */
    public function approveCourse(Request $request, $courseId)
    {
        $user = Auth::user();
        
        if ($user->role !== 'ADMIN') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $course = Course::find($courseId);
        if (!$course) {
            return response()->json(['message' => 'Course not found'], 404);
        }

        $course->status = 'PUBLISHED';
        $course->published_at = now();
        $course->save();

        // Log the action
        $this->logAction($request, 'INFO', 'Course Approved', [
            'course_id' => $course->id,
            'course_title' => $course->title,
            'teacher_id' => $course->teacher_id,
            'teacher_name' => $course->teacher ? ($course->teacher->first_name . ' ' . $course->teacher->last_name) : 'Unknown',
        ]);

        return response()->json(['message' => 'Course approved successfully', 'course' => $course]);
    }

    /**
     * Reject a course
     */
    public function rejectCourse(Request $request, $courseId)
    {
        $user = Auth::user();
        
        if ($user->role !== 'ADMIN') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $course = Course::find($courseId);
        if (!$course) {
            return response()->json(['message' => 'Course not found'], 404);
        }

        $reason = $request->input('reason', 'No reason provided');
        $course->status = 'DRAFT';
        $course->save();

        // Log the action
        $this->logAction($request, 'INFO', 'Course Rejected', [
            'course_id' => $course->id,
            'course_title' => $course->title,
            'teacher_id' => $course->teacher_id,
            'reason' => $reason,
        ]);

        return response()->json(['message' => 'Course rejected']);
    }
}
