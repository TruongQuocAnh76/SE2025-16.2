<?php

namespace App\Services;

use App\Repositories\TeacherApplicationRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

class TeacherApplicationService
{
    protected $applicationRepository;
    protected $userRepository;

    public function __construct(
        TeacherApplicationRepository $applicationRepository,
        UserRepository $userRepository
    ) {
        $this->applicationRepository = $applicationRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * Submit a new teacher application
     */
    public function submitApplication($userId, array $data, $certificateFile = null)
    {
        try {
            // Check if user already has a pending application
            if ($this->applicationRepository->hasPendingApplication($userId)) {
                throw new Exception('You already have a pending application. Please wait for it to be reviewed.');
            }

            // Validate user exists
            $user = $this->userRepository->getById($userId);
            if (!$user) {
                throw new Exception('User not found.');
            }

            // Handle certificate file upload
            $certificateFilePath = null;
            if ($certificateFile) {
                // Generate unique filename
                $extension = $certificateFile->getClientOriginalExtension();
                $filename = Str::uuid() . '.' . $extension;
                
                // Store file in storage/app (not using 'local' disk which points to private/)
                $certificateFilePath = $certificateFile->storeAs('certificates', $filename, 'public');
                
                Log::info("Certificate file uploaded", [
                    'filename' => $filename,
                    'path' => $certificateFilePath
                ]);
            }

            // Prepare application data
            $applicationData = [
                'id' => Str::uuid()->toString(),
                'user_id' => $userId,
                'status' => 'PENDING',
                // Personal Information
                'full_name' => $data['full_name'],
                'email' => $data['email'],
                'bio' => $data['bio'] ?? null,
                'gender' => $data['gender'] ?? null,
                'phone' => $data['phone'] ?? null,
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'country' => $data['country'] ?? null,
                'avatar_url' => $data['avatar_url'] ?? null,
                // Certificate Information
                'certificate_title' => $data['certificate_title'],
                'issuer' => $data['issuer'],
                'issue_date' => $data['issue_date'],
                'expiry_date' => $data['expiry_date'] ?? null,
                'certificate_file_path' => $certificateFilePath,
            ];

            // Create the application
            $application = $this->applicationRepository->create($applicationData);

            Log::info("Teacher application submitted", [
                'application_id' => $application->id,
                'user_id' => $userId,
                'certificate_file' => $certificateFilePath
            ]);

            return $application;
        } catch (Exception $e) {
            Log::error("Failed to submit teacher application", [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get all applications (admin)
     */
    public function getAllApplications()
    {
        return $this->applicationRepository->getAll();
    }

    /**
     * Get applications by status
     */
    public function getApplicationsByStatus($status)
    {
        return $this->applicationRepository->getByStatus($status);
    }

    /**
     * Get user's applications
     */
    public function getUserApplications($userId)
    {
        return $this->applicationRepository->getByUserId($userId);
    }

    /**
     * Get application by ID
     */
    public function getApplicationById($id)
    {
        return $this->applicationRepository->getById($id);
    }

    /**
     * Approve an application
     */
    public function approveApplication($applicationId, $reviewerId)
    {
        try {
            $application = $this->applicationRepository->getById($applicationId);

            if ($application->status !== 'PENDING') {
                throw new Exception('Only pending applications can be approved.');
            }

            // Update application status
            $updatedApplication = $this->applicationRepository->update($applicationId, [
                'status' => 'APPROVED',
                'reviewed_by' => $reviewerId,
                'reviewed_at' => now(),
                'rejection_reason' => null,
            ]);

            // Update user role to TEACHER
            $this->userRepository->update($application->user_id, [
                'role' => 'TEACHER'
            ]);

            Log::info("Teacher application approved", [
                'application_id' => $applicationId,
                'user_id' => $application->user_id,
                'reviewer_id' => $reviewerId
            ]);

            return $updatedApplication;
        } catch (Exception $e) {
            Log::error("Failed to approve teacher application", [
                'application_id' => $applicationId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Reject an application
     */
    public function rejectApplication($applicationId, $reviewerId, $reason)
    {
        try {
            $application = $this->applicationRepository->getById($applicationId);

            if ($application->status !== 'PENDING') {
                throw new Exception('Only pending applications can be rejected.');
            }

            // Update application status
            $updatedApplication = $this->applicationRepository->update($applicationId, [
                'status' => 'REJECTED',
                'reviewed_by' => $reviewerId,
                'reviewed_at' => now(),
                'rejection_reason' => $reason,
            ]);

            Log::info("Teacher application rejected", [
                'application_id' => $applicationId,
                'user_id' => $application->user_id,
                'reviewer_id' => $reviewerId,
                'reason' => $reason
            ]);

            return $updatedApplication;
        } catch (Exception $e) {
            Log::error("Failed to reject teacher application", [
                'application_id' => $applicationId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
