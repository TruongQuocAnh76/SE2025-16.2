<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Services\TeacherApplicationService;
use Exception;

class TeacherApplicationController extends Controller
{
    protected $applicationService;

    public function __construct(TeacherApplicationService $applicationService)
    {
        $this->applicationService = $applicationService;
    }

    /**
     * Submit a teacher application
     * 
     * @OA\Post(
     *     path="/api/teacher-applications/submit",
     *     summary="Submit a teacher application",
     *     description="Submit an application to become a teacher by providing personal information and certification details",
     *     operationId="submitTeacherApplication",
     *     tags={"Teacher Applications"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"full_name", "email", "certificate_title", "issuer", "issue_date", "certificate_file"},
     *                 @OA\Property(property="full_name", type="string", example="John Doe"),
     *                 @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     *                 @OA\Property(property="bio", type="string", example="Experienced educator with 10 years of teaching", nullable=true),
     *                 @OA\Property(property="gender", type="string", enum={"MALE", "FEMALE", "OTHER"}, example="MALE", nullable=true),
     *                 @OA\Property(property="phone", type="string", example="+1234567890", nullable=true),
     *                 @OA\Property(property="date_of_birth", type="string", format="date", example="1990-01-15", nullable=true),
     *                 @OA\Property(property="country", type="string", example="United States", nullable=true),
     *                 @OA\Property(property="avatar_url", type="string", example="https://example.com/avatar.jpg", nullable=true),
     *                 @OA\Property(property="certificate_title", type="string", example="Master of Computer Science"),
     *                 @OA\Property(property="issuer", type="string", example="Stanford University"),
     *                 @OA\Property(property="issue_date", type="string", format="date", example="2023-05-15"),
     *                 @OA\Property(property="expiry_date", type="string", format="date", example="2028-05-15", nullable=true),
     *                 @OA\Property(property="certificate_file", type="string", format="binary", description="Certificate document file (PDF, JPG, PNG, max 10MB)")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Application submitted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Application submitted successfully"),
     *             @OA\Property(property="application", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error or pending application exists"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function submit(Request $request)
    {
        // Debug logging
        \Log::info('=== TEACHER APPLICATION SUBMIT START ===');
        \Log::info('Auth user ID: ' . (Auth::id() ?? 'NULL'));
        \Log::info('Auth check: ' . (Auth::check() ? 'TRUE' : 'FALSE'));
        \Log::info('Request has file: ' . ($request->hasFile('certificate_file') ? 'YES' : 'NO'));
        if ($request->hasFile('certificate_file')) {
            $file = $request->file('certificate_file');
            \Log::info('File info: name=' . $file->getClientOriginalName() . ', size=' . $file->getSize() . ', mime=' . $file->getMimeType());
        }
        \Log::info('All request data keys: ' . implode(', ', array_keys($request->all())));
        
        $validator = Validator::make($request->all(), [
            // Personal Information
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'bio' => 'nullable|string|max:1000',
            'gender' => 'nullable|in:MALE,FEMALE,OTHER',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date|before:today',
            'country' => 'nullable|string|max:100',
            'avatar_url' => 'nullable|string|max:500',
            
            // Certificate Information
            'certificate_title' => 'required|string|max:255',
            'issuer' => 'required|string|max:255',
            'issue_date' => 'required|date|before_or_equal:today',
            'expiry_date' => 'nullable|date|after:issue_date',
            'certificate_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240', // Required, max 10MB
        ]);

        if ($validator->fails()) {
            \Log::error('Validation failed: ' . json_encode($validator->errors()));
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $userId = Auth::id();
            \Log::info('About to call submitApplication service');
            $application = $this->applicationService->submitApplication($userId, $request->all(), $request->file('certificate_file'));

            return response()->json([
                'message' => 'Application submitted successfully',
                'application' => $application
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to submit application',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get current user's applications
     * 
     * @OA\Get(
     *     path="/api/teacher-applications/my-applications",
     *     summary="Get my teacher applications",
     *     description="Retrieve all applications submitted by the authenticated user",
     *     operationId="getMyApplications",
     *     tags={"Teacher Applications"},
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of user's applications",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="string", format="uuid"),
     *                 @OA\Property(property="user_id", type="string", format="uuid"),
     *                 @OA\Property(property="status", type="string", enum={"PENDING", "APPROVED", "REJECTED"}),
     *                 @OA\Property(property="certificate_title", type="string"),
     *                 @OA\Property(property="issuer", type="string"),
     *                 @OA\Property(property="issue_date", type="string", format="date"),
     *                 @OA\Property(property="expiry_date", type="string", format="date", nullable=true),
     *                 @OA\Property(property="reviewed_by", type="string", format="uuid", nullable=true),
     *                 @OA\Property(property="reviewed_at", type="string", format="date-time", nullable=true),
     *                 @OA\Property(property="rejection_reason", type="string", nullable=true),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function myApplications()
    {
        try {
            $userId = Auth::id();
            $applications = $this->applicationService->getUserApplications($userId);

            return response()->json([
                'applications' => $applications
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve applications',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all applications (Admin only)
     * 
     * @OA\Get(
     *     path="/api/teacher-applications",
     *     summary="Get all teacher applications",
     *     description="Retrieve all teacher applications (admin only)",
     *     operationId="getAllApplications",
     *     tags={"Teacher Applications"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"PENDING", "APPROVED", "REJECTED"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of applications",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="string", format="uuid"),
     *                 @OA\Property(property="user_id", type="string", format="uuid"),
     *                 @OA\Property(property="status", type="string", enum={"PENDING", "APPROVED", "REJECTED"}),
     *                 @OA\Property(property="certificate_title", type="string"),
     *                 @OA\Property(property="issuer", type="string"),
     *                 @OA\Property(property="issue_date", type="string", format="date"),
     *                 @OA\Property(property="expiry_date", type="string", format="date", nullable=true),
     *                 @OA\Property(property="reviewed_by", type="string", format="uuid", nullable=true),
     *                 @OA\Property(property="reviewed_at", type="string", format="date-time", nullable=true),
     *                 @OA\Property(property="rejection_reason", type="string", nullable=true),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time"),
     *                 @OA\Property(
     *                     property="user",
     *                     type="object",
     *                     @OA\Property(property="id", type="string", format="uuid"),
     *                     @OA\Property(property="first_name", type="string"),
     *                     @OA\Property(property="last_name", type="string"),
     *                     @OA\Property(property="email", type="string", format="email")
     *                 ),
     *                 @OA\Property(
     *                     property="reviewer",
     *                     type="object",
     *                     nullable=true,
     *                     @OA\Property(property="id", type="string", format="uuid"),
     *                     @OA\Property(property="first_name", type="string"),
     *                     @OA\Property(property="last_name", type="string")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Admin only"
     *     )
     * )
     */
    public function index(Request $request)
    {
        try {
            // Check if user is admin
            if (Auth::user()->role !== 'ADMIN') {
                return response()->json([
                    'message' => 'Unauthorized. Admin access required.'
                ], 403);
            }

            $status = $request->query('status');
            
            if ($status) {
                $applications = $this->applicationService->getApplicationsByStatus($status);
            } else {
                $applications = $this->applicationService->getAllApplications();
            }

            return response()->json($applications);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve applications',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get application by ID
     * 
     * @OA\Get(
     *     path="/api/teacher-applications/{id}",
     *     summary="Get application details",
     *     description="Retrieve a specific teacher application by ID",
     *     operationId="getApplicationById",
     *     tags={"Teacher Applications"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Application details",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="string", format="uuid"),
     *             @OA\Property(property="user_id", type="string", format="uuid"),
     *             @OA\Property(property="status", type="string", enum={"PENDING", "APPROVED", "REJECTED"}),
     *             @OA\Property(property="certificate_title", type="string"),
     *             @OA\Property(property="issuer", type="string"),
     *             @OA\Property(property="issue_date", type="string", format="date"),
     *             @OA\Property(property="expiry_date", type="string", format="date", nullable=true),
     *             @OA\Property(property="reviewed_by", type="string", format="uuid", nullable=true),
     *             @OA\Property(property="reviewed_at", type="string", format="date-time", nullable=true),
     *             @OA\Property(property="rejection_reason", type="string", nullable=true),
     *             @OA\Property(property="created_at", type="string", format="date-time"),
     *             @OA\Property(property="updated_at", type="string", format="date-time"),
     *             @OA\Property(
     *                 property="user",
     *                 type="object",
     *                 @OA\Property(property="id", type="string", format="uuid"),
     *                 @OA\Property(property="first_name", type="string"),
     *                 @OA\Property(property="last_name", type="string"),
     *                 @OA\Property(property="email", type="string", format="email")
     *             ),
     *             @OA\Property(
     *                 property="reviewer",
     *                 type="object",
     *                 nullable=true,
     *                 @OA\Property(property="id", type="string", format="uuid"),
     *                 @OA\Property(property="first_name", type="string"),
     *                 @OA\Property(property="last_name", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Application not found"
     *     )
     * )
     */
    public function show($id)
    {
        try {
            $application = $this->applicationService->getApplicationById($id);
            
            // Check if user has permission to view this application
            $user = Auth::user();
            if ($user->role !== 'ADMIN' && $application->user_id !== $user->id) {
                return response()->json([
                    'message' => 'Unauthorized to view this application.'
                ], 403);
            }

            return response()->json($application);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve application',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Approve an application (Admin only)
     * 
     * @OA\Post(
     *     path="/api/teacher-applications/{id}/approve",
     *     summary="Approve teacher application",
     *     description="Approve a pending teacher application and upgrade user to teacher role",
     *     operationId="approveApplication",
     *     tags={"Teacher Applications"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Application approved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="application", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Admin only"
     *     )
     * )
     */
    public function approve($id)
    {
        try {
            // Check if user is admin
            if (Auth::user()->role !== 'ADMIN') {
                return response()->json([
                    'message' => 'Unauthorized. Admin access required.'
                ], 403);
            }

            $reviewerId = Auth::id();
            $application = $this->applicationService->approveApplication($id, $reviewerId);

            return response()->json([
                'message' => 'Application approved successfully',
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
     * Reject an application (Admin only)
     * 
     * @OA\Post(
     *     path="/api/teacher-applications/{id}/reject",
     *     summary="Reject teacher application",
     *     description="Reject a pending teacher application with a reason",
     *     operationId="rejectApplication",
     *     tags={"Teacher Applications"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"rejection_reason"},
     *             @OA\Property(property="rejection_reason", type="string", example="Insufficient qualifications")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Application rejected successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="application", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Admin only"
     *     )
     * )
     */
    public function reject(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            // Check if user is admin
            if (Auth::user()->role !== 'ADMIN') {
                return response()->json([
                    'message' => 'Unauthorized. Admin access required.'
                ], 403);
            }

            $reviewerId = Auth::id();
            $reason = $request->input('rejection_reason');
            $application = $this->applicationService->rejectApplication($id, $reviewerId, $reason);

            return response()->json([
                'message' => 'Application rejected successfully',
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
     * Download certificate file
     * 
     * @OA\Get(
     *     path="/api/teacher-applications/{id}/certificate",
     *     summary="Download certificate file",
     *     description="Download the certificate document attached to a teacher application",
     *     operationId="downloadCertificate",
     *     tags={"Teacher Applications"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Certificate file",
     *         @OA\MediaType(
     *             mediaType="application/octet-stream"
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Application or certificate file not found"
     *     )
     * )
     */
    public function downloadCertificate($id)
    {
        try {
            $application = $this->applicationService->getApplicationById($id);
            
            if (!$application) {
                return response()->json([
                    'message' => 'Application not found'
                ], 404);
            }

            if (!$application->certificate_file_path) {
                return response()->json([
                    'message' => 'Certificate file not found'
                ], 404);
            }

            // Check if file exists in storage (use 'public' disk)
            if (!\Storage::disk('public')->exists($application->certificate_file_path)) {
                return response()->json([
                    'message' => 'Certificate file does not exist in storage'
                ], 404);
            }

            // Return file download response
            return \Storage::disk('public')->download($application->certificate_file_path);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to download certificate',
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
