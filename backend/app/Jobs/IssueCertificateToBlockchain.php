<?php

namespace App\Jobs;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

class IssueCertificateToBlockchain implements ShouldQueue
{
    use Queueable;

    public $timeout = 60; // 1 minute timeout
    public $tries = 3; // Retry up to 3 times

    private string $userId;
    private string $courseId;

    /**
     * Create a new job instance.
     */
    public function __construct(string $userId, string $courseId)
    {
        $this->userId = $userId;
        $this->courseId = $courseId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info("Starting certificate issuance job", [
                'user_id' => $this->userId,
                'course_id' => $this->courseId,
                'attempt' => $this->attempts()
            ]);

            $user = User::findOrFail($this->userId);
            $course = Course::findOrFail($this->courseId);

            Log::info("Found user and course", [
                'user_name' => $user->first_name . ' ' . $user->last_name,
                'course_title' => $course->title
            ]);

            // Check if certificate already exists
            $existingCertificate = Certificate::where('student_id', $this->userId)
                ->where('course_id', $this->courseId)
                ->first();

            if ($existingCertificate && $existingCertificate->blockchain_transaction_hash) {
                Log::info("Certificate already exists on blockchain", [
                    'user_id' => $this->userId,
                    'course_id' => $this->courseId,
                    'blockchain_transaction_hash' => $existingCertificate->blockchain_transaction_hash
                ]);
                return;
            }

            // Create or get certificate record
            if (!$existingCertificate) {
                // Generate a deterministic certificate number based on user and course IDs
                $certificateNumber = 'CERT-' . strtoupper(substr(hash('md5', $this->userId . $this->courseId), 0, 8));
                
                $certificate = Certificate::create([
                    'id' => Str::uuid(),
                    'student_id' => $this->userId,
                    'course_id' => $this->courseId,
                    'certificate_number' => $certificateNumber,
                    'issued_at' => now(),
                    'status' => 'PENDING',
                    'final_score' => 100.00,
                    'certificate_data' => [
                        'student_name' => $user->first_name . ' ' . $user->last_name,
                        'student_email' => $user->email,
                        'course_title' => $course->title,
                        'completion_date' => now()->toDateString(),
                        'issued_by' => config('app.name'),
                    ],
                ]);
                
                Log::info("Created new certificate record", [
                    'certificate_id' => $certificate->id,
                    'certificate_number' => $certificate->certificate_number
                ]);
            } else {
                $certificate = $existingCertificate;
                
                Log::info("Using existing certificate record", [
                    'certificate_id' => $certificate->id,
                    'certificate_number' => $certificate->certificate_number,
                    'current_status' => $certificate->blockchain_status
                ]);
            }

            // Call blockchain microservice to issue certificate
            $blockchainServiceUrl = config('services.blockchain.url', 'http://blockchain:3001');
            
            Log::info("Attempting to issue certificate to blockchain", [
                'user_id' => $this->userId,
                'course_id' => $this->courseId,
                'certificate_number' => $certificate->certificate_number,
                'blockchain_url' => $blockchainServiceUrl,
                'attempt' => $this->attempts()
            ]);
            
            // Add retry logic for connection issues
            $response = Http::retry(2, 1000) // Retry 2 times with 1 second delay
                ->timeout(30)
                ->post("{$blockchainServiceUrl}/v1/certs/issue", [
                'certificateNumber' => $certificate->certificate_number,
                'studentId' => $user->id,
                'studentAddress' => $user->wallet_address ?? '0x' . str_pad(dechex(abs(crc32($user->id))), 40, '0', STR_PAD_LEFT),
                'courseId' => $course->id,
                'pdfHash' => $certificate->pdf_hash ?? hash('sha256', $certificate->certificate_number),
                'finalScore' => $certificate->final_score,
                'expiresAt' => 0, // No expiration
                'metadata' => json_encode([
                    'course_title' => $course->title,
                    'student_name' => $user->first_name . ' ' . $user->last_name,
                    'completion_date' => now()->toISOString(),
                    'platform' => config('app.name'),
                ])
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                
                // Check if the response has the expected structure
                if (!isset($responseData['data']['transactionHash'])) {
                    throw new Exception("Invalid response from blockchain service: missing transactionHash");
                }
                
                // Update certificate with blockchain information
                $certificate->update([
                    'blockchain_transaction_hash' => $responseData['data']['transactionHash'],
                    'blockchain_status' => 'issued',
                    'blockchain_issued_at' => now(),
                ]);

                Log::info("Certificate issued to blockchain successfully", [
                    'user_id' => $this->userId,
                    'course_id' => $this->courseId,
                    'transaction_hash' => $responseData['data']['transactionHash'],
                    'certificate_data' => $responseData['data']['certificate'] ?? null
                ]);
            } else {
                $errorMessage = "Blockchain service returned error: {$response->status()} - {$response->body()}";
                
                Log::error("Blockchain service error", [
                    'user_id' => $this->userId,
                    'course_id' => $this->courseId,
                    'status_code' => $response->status(),
                    'response_body' => $response->body(),
                    'attempt' => $this->attempts()
                ]);
                
                throw new Exception($errorMessage);
            }

        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            $isConnectionError = str_contains($errorMessage, 'Could not resolve host') || 
                                str_contains($errorMessage, 'Connection refused') ||
                                str_contains($errorMessage, 'cURL error');
            
            Log::error("Failed to issue certificate to blockchain", [
                'user_id' => $this->userId,
                'course_id' => $this->courseId,
                'error' => $errorMessage,
                'attempt' => $this->attempts(),
                'is_connection_error' => $isConnectionError
            ]);

            // If this is the final attempt, update certificate status
            if ($this->attempts() >= $this->tries) {
                if (isset($certificate)) {
                    $certificate->update([
                        'blockchain_status' => $isConnectionError ? 'connection_failed' : 'failed',
                        'blockchain_error' => $errorMessage
                    ]);
                }
            }

            throw $e; // Re-throw to trigger retry
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Exception $exception): void
    {
        Log::error("Certificate issuance job failed permanently", [
            'user_id' => $this->userId,
            'course_id' => $this->courseId,
            'error' => $exception->getMessage()
        ]);

        // Update certificate status to failed if it exists
        $certificate = Certificate::where('student_id', $this->userId)
            ->where('course_id', $this->courseId)
            ->first();

        if ($certificate) {
            $certificate->update(['blockchain_status' => 'failed']);
        }
    }
}
