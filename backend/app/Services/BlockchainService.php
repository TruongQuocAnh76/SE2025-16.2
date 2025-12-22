<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BlockchainService
{
    protected $blockchainApiUrl;

    public function __construct()
    {
        $this->blockchainApiUrl = env('BLOCKCHAIN_API_URL', 'http://blockchain:3001');
    }

    /**
     * Issue a certificate to the blockchain
     */
    public function issueCertificate($certificateNumber, $studentAddress, $courseTitle, $pdfHash = null)
    {
        try {
            $response = Http::timeout(30)->post("{$this->blockchainApiUrl}/v1/certs/issue", [
                'certificate_number' => $certificateNumber,
                'student_address' => $studentAddress,
                'course_title' => $courseTitle,
                'pdf_hash' => $pdfHash
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            Log::error('Blockchain API Error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return [
                'success' => false,
                'error' => 'Failed to issue certificate to blockchain',
                'details' => $response->json()
            ];
        } catch (\Exception $e) {
            Log::error('Blockchain Service Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => 'Blockchain service unavailable',
                'details' => $e->getMessage()
            ];
        }
    }

    /**
     * Verify a certificate on the blockchain
     */
    public function verifyCertificate($certificateNumber, $pdfHash = null)
    {
        try {
            $response = Http::timeout(30)->post("{$this->blockchainApiUrl}/v1/certs/verify", [
                'certificate_number' => $certificateNumber,
                'pdf_hash' => $pdfHash
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            return [
                'success' => false,
                'error' => 'Failed to verify certificate',
                'details' => $response->json()
            ];
        } catch (\Exception $e) {
            Log::error('Blockchain Verification Exception', [
                'message' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => 'Blockchain service unavailable',
                'details' => $e->getMessage()
            ];
        }
    }

    /**
     * Get blockchain status for a certificate
     */
    public function getBlockchainStatus($certificateNumber)
    {
        try {
            $response = Http::timeout(30)->get("{$this->blockchainApiUrl}/v1/certs/{$certificateNumber}");

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            return [
                'success' => false,
                'error' => 'Failed to get blockchain status',
                'details' => $response->json()
            ];
        } catch (\Exception $e) {
            Log::error('Blockchain Status Exception', [
                'message' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => 'Blockchain service unavailable',
                'details' => $e->getMessage()
            ];
        }
    }

    /**
     * Retry certificate issuance
     */
    public function retryCertificateIssuance($certificateNumber)
    {
        try {
            $response = Http::timeout(30)->post("{$this->blockchainApiUrl}/v1/certs/revoke", [
                'certificate_number' => $certificateNumber
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            return [
                'success' => false,
                'error' => 'Failed to retry certificate issuance',
                'details' => $response->json()
            ];
        } catch (\Exception $e) {
            Log::error('Blockchain Retry Exception', [
                'message' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => 'Blockchain service unavailable',
                'details' => $e->getMessage()
            ];
        }
    }
}