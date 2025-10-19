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

class CertificateController extends Controller
{
    /**
     * ✅ Lấy danh sách chứng chỉ của học viên hiện tại
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
     * ✅ Tạo chứng chỉ sau khi học viên hoàn thành khóa học
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
     * ✅ Xem chi tiết chứng chỉ
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
     * ✅ Thu hồi chứng chỉ (giảng viên hoặc admin)
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
     * ✅ Kiểm tra tính hợp lệ chứng chỉ (verify hash)
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
     * ✅ Liên kết chứng chỉ với giao dịch blockchain (sau khi ghi on-chain)
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
