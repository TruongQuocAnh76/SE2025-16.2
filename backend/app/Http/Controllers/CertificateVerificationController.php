<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use App\Models\Certificate;
use App\Services\BlockchainService;

/**
 * @OA\Tag(
 *     name="Certificate Verification",
 *     description="API endpoints for certificate verification"
 * )
 */
class CertificateVerificationController extends Controller
{
    protected $blockchainService;

    public function __construct(BlockchainService $blockchainService)
    {
        $this->blockchainService = $blockchainService;
    }

    /**
     * @OA\Post(
     *     path="/certificates/verify",
     *     summary="Verify a certificate using blockchain",
     *     tags={"Certificate Verification"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"certificate_number"},
     *             @OA\Property(property="certificate_number", type="string", example="CERT-WEB-2024-001"),
     *             @OA\Property(property="pdf_hash", type="string", example="sha256hashofpdf", description="Optional PDF hash for additional verification")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Certificate verification result",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="is_valid", type="boolean"),
     *                 @OA\Property(property="is_revoked", type="boolean"),
     *                 @OA\Property(property="issued_at", type="string", format="date-time"),
     *                 @OA\Property(property="expires_at", type="string", format="date-time", nullable=true),
     *                 @OA\Property(property="certificate", type="object",
     *                     @OA\Property(property="certificate_number", type="string"),
     *                     @OA\Property(property="student_name", type="string"),
     *                     @OA\Property(property="course_title", type="string"),
     *                     @OA\Property(property="final_score", type="number"),
     *                     @OA\Property(property="blockchain_status", type="string"),
     *                     @OA\Property(property="transaction_hash", type="string")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=404, description="Certificate not found"),
     *     @OA\Response(response=503, description="Blockchain service unavailable")
     * )
     */
    public function verify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'certificate_number' => 'required|string|max:255',
            'pdf_hash' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $certificateNumber = $request->certificate_number;
        $pdfHash = $request->pdf_hash;

        try {
            // First check if certificate exists in our database
            $certificate = Certificate::where('certificate_number', $certificateNumber)
                ->with(['student:id,first_name,last_name', 'course:id,title'])
                ->first();

            if (!$certificate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Certificate not found in our records'
                ], 404);
            }

            // Verify with blockchain service
            $blockchainVerification = $this->blockchainService->verifyCertificate($certificateNumber, $pdfHash);

            if (!$blockchainVerification['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to verify certificate on blockchain',
                    'error' => $blockchainVerification['error'] ?? 'Unknown error'
                ], 503);
            }

            $verification = $blockchainVerification['data'];

            // Prepare response data
            $responseData = [
                'is_valid' => $verification['isValid'],
                'is_revoked' => $verification['isRevoked'],
                'issued_at' => $verification['issuedAt'] ? date('Y-m-d H:i:s', $verification['issuedAt']) : null,
                'expires_at' => $verification['expiresAt'] ? date('Y-m-d H:i:s', $verification['expiresAt']) : null,
                'certificate' => [
                    'certificate_number' => $certificate->certificate_number,
                    'student_name' => $certificate->student ? 
                        $certificate->student->first_name . ' ' . $certificate->student->last_name : 'Unknown',
                    'course_title' => $certificate->course->title ?? 'Unknown Course',
                    'final_score' => $certificate->final_score,
                    'blockchain_status' => $certificate->blockchain_status,
                    'transaction_hash' => $certificate->blockchain_transaction_hash,
                    'issued_date' => $certificate->issued_at,
                    'pdf_url' => $certificate->pdf_url,
                ]
            ];

            $message = 'Certificate verified successfully';
            if ($verification['isRevoked']) {
                $message = 'Certificate has been revoked';
            } elseif (!$verification['isValid']) {
                $message = 'Certificate is not valid';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $responseData
            ]);

        } catch (\Exception $e) {
            \Log::error('Certificate verification error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while verifying the certificate',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/certificates/{certificate_number}/blockchain-status",
     *     summary="Get blockchain status of a certificate",
     *     tags={"Certificate Verification"},
     *     @OA\Parameter(
     *         name="certificate_number",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Blockchain status information",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="blockchain_status", type="string"),
     *                 @OA\Property(property="transaction_hash", type="string"),
     *                 @OA\Property(property="block_number", type="integer"),
     *                 @OA\Property(property="confirmations", type="integer"),
     *                 @OA\Property(property="gas_used", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Certificate not found")
     * )
     */
    public function getBlockchainStatus($certificateNumber)
    {
        try {
            $certificate = Certificate::where('certificate_number', $certificateNumber)->first();

            if (!$certificate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Certificate not found'
                ], 404);
            }

            $statusData = [
                'blockchain_status' => $certificate->blockchain_status,
                'transaction_hash' => $certificate->blockchain_transaction_hash,
                'block_number' => $certificate->blockchain_block_number,
                'confirmations' => $certificate->blockchain_confirmations,
                'gas_used' => $certificate->blockchain_gas_used
            ];

            // If we have a transaction hash, get latest status from blockchain
            if ($certificate->blockchain_transaction_hash) {
                $blockchainStatus = $this->blockchainService->getTransactionStatus($certificate->blockchain_transaction_hash);
                
                if ($blockchainStatus['success']) {
                    $txData = $blockchainStatus['data'];
                    $statusData['confirmations'] = $txData['confirmations'] ?? 0;
                    $statusData['block_number'] = $txData['blockNumber'] ?? null;
                    $statusData['gas_used'] = $txData['gasUsed'] ?? null;
                }
            }

            return response()->json([
                'success' => true,
                'data' => $statusData
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching blockchain status: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching blockchain status'
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/certificates/{certificate_number}/retry-blockchain",
     *     summary="Retry failed blockchain certificate issuance",
     *     tags={"Certificate Verification"},
     *     @OA\Parameter(
     *         name="certificate_number",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Certificate issuance job queued",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Certificate not found"),
     *     @OA\Response(response=422, description="Certificate cannot be retried")
     * )
     */
    public function retryBlockchainIssuance($certificateNumber)
    {
        try {
            $certificate = Certificate::where('certificate_number', $certificateNumber)->first();

            if (!$certificate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Certificate not found'
                ], 404);
            }

            // Only allow retry for failed certificates
            if ($certificate->blockchain_status !== 'failed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Certificate issuance can only be retried for failed certificates'
                ], 422);
            }

            // Reset blockchain status and dispatch job
            $certificate->update([
                'blockchain_status' => 'pending',
                'blockchain_transaction_hash' => null,
                'blockchain_error' => null
            ]);

            // Dispatch the job again
            \App\Jobs\IssueCertificateToBlockchain::dispatch($certificate->student_id, $certificate->course_id);

            return response()->json([
                'success' => true,
                'message' => 'Certificate issuance job has been queued for retry'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error retrying blockchain issuance: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrying certificate issuance'
            ], 500);
        }
    }
}