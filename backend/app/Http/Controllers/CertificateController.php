<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\QuizAttempt;
use App\Models\BlockchainTransaction;
use PDF; // nếu bạn dùng barryvdh/laravel-dompdf để tạo PDF

/**
 * @OA\Server(
 *     url="http://localhost:8000/api",
 *     description="Local development server"
 * )
 */
class CertificateController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/certificates/mine",
     *     summary="Get current user's certificates",
     *     description="Retrieve all certificates issued to the authenticated user",
     *     operationId="getMyCertificates",
     *     tags={"Certificates"},
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of user's certificates",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Certificate")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function myCertificates()
    {
        $certificates = Certificate::with(['course'])
            ->where('student_id', Auth::id())
            ->orderByDesc('issued_at')
            ->get();

        return response()->json($certificates);
    }

    /**
     * @OA\Post(
     *     path="/api/certificates/issue/{courseId}",
     *     summary="Issue certificate for course completion",
     *     description="Issue a certificate after student completes a course and passes the final quiz",
     *     operationId="issueCertificate",
     *     tags={"Certificates"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="courseId",
     *         in="path",
     *         required=true,
     *         description="Course ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Certificate issued successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="certificate", ref="#/components/schemas/Certificate")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request - student hasn't passed final quiz or certificate already exists",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Course not found"
     *     )
     * )
     */
    public function issueCertificate(Request $request, $courseId)
    {
        $studentId = Auth::id();
        $course = Course::findOrFail($courseId);

        // Kiểm tra học viên có đủ điều kiện không
        $finalAttempt = QuizAttempt::where('student_id', $studentId)
            ->whereHas('quiz', function ($q) use ($courseId) {
                $q->where('course_id', $courseId)->where('quiz_type', 'FINAL');
            })
            ->orderByDesc('created_at')
            ->first();

        if (!$finalAttempt || !$finalAttempt->is_passed) {
            return response()->json(['error' => 'Bạn chưa vượt qua bài kiểm tra cuối cùng.'], 400);
        }

        // Tránh cấp trùng
        $existing = Certificate::where('student_id', $studentId)
            ->where('course_id', $courseId)
            ->first();

        if ($existing) {
            return response()->json(['message' => 'Chứng chỉ đã được cấp trước đó.', 'certificate' => $existing]);
        }

        // Tạo certificate_number ngắn gọn (ví dụ: CERT-2025-ABCDE)
        $certNumber = 'CERT-' . now()->format('Y') . '-' . strtoupper(Str::random(6));

        // Tạo PDF (tùy bạn triển khai — đây là ví dụ)
        $pdfPath = "certificates/{$certNumber}.pdf";
        $pdf = PDF::loadView('certificates.template', [
            'student' => Auth::user()->name,
            'course' => $course->title,
            'score' => $finalAttempt->score,
            'issue_date' => now()->format('d/m/Y'),
            'certificate_number' => $certNumber
        ]);

        Storage::disk('public')->put($pdfPath, $pdf->output());
        $pdfUrl = Storage::disk('public')->url($pdfPath);
        $pdfHash = Hash::make(file_get_contents(storage_path("app/public/{$pdfPath}")));

        // Lưu vào DB
        $certificate = Certificate::create([
            'id' => Str::uuid(),
            'certificate_number' => $certNumber,
            'student_id' => $studentId,
            'course_id' => $courseId,
            'status' => 'ISSUED',
            'final_score' => $finalAttempt->score,
            'pdf_url' => $pdfUrl,
            'pdf_hash' => $pdfHash,
            'issued_at' => now(),
        ]);

        return response()->json([
            'message' => 'Chứng chỉ đã được cấp thành công.',
            'certificate' => $certificate
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/certificates/{certificateId}",
     *     summary="Get certificate details",
     *     description="Retrieve detailed information about a specific certificate",
     *     operationId="getCertificate",
     *     tags={"Certificates"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="certificateId",
     *         in="path",
     *         required=true,
     *         description="Certificate ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Certificate details",
     *         @OA\JsonContent(ref="#/components/schemas/Certificate")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - user doesn't own this certificate",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Certificate not found"
     *     )
     * )
     */
    public function show($certificateId)
    {
        $certificate = Certificate::with(['course', 'student'])
            ->findOrFail($certificateId);

        // Kiểm tra quyền xem (chỉ chủ sở hữu hoặc giảng viên)
        if ($certificate->student_id !== Auth::id()) {
            return response()->json(['error' => 'Bạn không có quyền xem chứng chỉ này.'], 403);
        }

        return response()->json($certificate);
    }

    /**
     * @OA\Post(
     *     path="/api/certificates/{certificateId}/revoke",
     *     summary="Revoke certificate",
     *     description="Revoke a certificate (only course teacher or admin can perform this action)",
     *     operationId="revokeCertificate",
     *     tags={"Certificates"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="certificateId",
     *         in="path",
     *         required=true,
     *         description="Certificate ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="reason", type="string", description="Reason for revocation")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Certificate revoked successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - user is not the course teacher",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Certificate not found"
     *     )
     * )
     */
    public function revoke(Request $request, $certificateId)
    {
        $certificate = Certificate::findOrFail($certificateId);
        $course = Course::find($certificate->course_id);

        if (Auth::id() !== $course->teacher_id) {
            return response()->json(['error' => 'Không có quyền thu hồi chứng chỉ.'], 403);
        }

        $certificate->update([
            'status' => 'REVOKED',
            'revoked_at' => now(),
            'revocation_reason' => $request->input('reason', 'Không rõ lý do.'),
        ]);

        return response()->json(['message' => 'Đã thu hồi chứng chỉ.']);
    }

    /**
     * @OA\Get(
     *     path="/api/certificates/verify/{certificateNumber}",
     *     summary="Verify certificate validity",
     *     description="Verify the authenticity of a certificate using its certificate number",
     *     operationId="verifyCertificate",
     *     tags={"Certificates"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="certificateNumber",
     *         in="path",
     *         required=true,
     *         description="Certificate number (e.g., CERT-2025-ABCDEF)",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Certificate verification result",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="valid", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="certificate", ref="#/components/schemas/Certificate", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function verifyCertificate($certificateNumber)
    {
        $certificate = Certificate::where('certificate_number', $certificateNumber)->first();

        if (!$certificate) {
            return response()->json(['valid' => false, 'message' => 'Không tìm thấy chứng chỉ.']);
        }

        if ($certificate->status !== 'ISSUED') {
            return response()->json(['valid' => false, 'message' => 'Chứng chỉ không còn hiệu lực.']);
        }

        // Kiểm tra hash
        $localPdf = file_get_contents(storage_path('app/public/' . str_replace(Storage::disk('public')->url(''), '', $certificate->pdf_url)));
        $isValid = Hash::check($localPdf, $certificate->pdf_hash);

        return response()->json([
            'valid' => $isValid,
            'certificate' => $certificate
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/certificates/{certificateId}/attach-blockchain",
     *     summary="Attach blockchain transaction data",
     *     description="Link a certificate with its blockchain transaction data after on-chain recording",
     *     operationId="attachBlockchainData",
     *     tags={"Certificates"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="certificateId",
     *         in="path",
     *         required=true,
     *         description="Certificate ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"transaction_hash", "network", "certificate_hash"},
     *             @OA\Property(property="transaction_hash", type="string", description="Blockchain transaction hash"),
     *             @OA\Property(property="network", type="string", enum={"ETHEREUM", "POLYGON", "HYPERLEDGER"}),
     *             @OA\Property(property="certificate_hash", type="string", description="Hash of certificate data stored on blockchain"),
     *             @OA\Property(property="metadata", type="object", description="Additional blockchain metadata", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Blockchain data attached successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="transaction", ref="#/components/schemas/BlockchainTransaction")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error or duplicate transaction hash",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Certificate not found"
     *     )
     * )
     */
    public function attachBlockchainData(Request $request, $certificateId)
    {
        $certificate = Certificate::findOrFail($certificateId);

        $request->validate([
            'transaction_hash' => 'required|string|unique:blockchain_transactions',
            'network' => 'required|in:ETHEREUM,POLYGON,HYPERLEDGER',
            'certificate_hash' => 'required|string',
        ]);

        $tx = BlockchainTransaction::create([
            'id' => Str::uuid(),
            'transaction_hash' => $request->transaction_hash,
            'network' => $request->network,
            'certificate_hash' => $request->certificate_hash,
            'metadata' => $request->metadata,
            'status' => 'CONFIRMED',
            'certificate_id' => $certificate->id,
            'confirmed_at' => now(),
        ]);

        return response()->json([
            'message' => 'Đã liên kết chứng chỉ với giao dịch blockchain.',
            'transaction' => $tx
        ]);
    }
}
