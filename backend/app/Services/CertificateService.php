<?php 

namespace App\Services;

use App\Repositories\CertificateRepository;
use App\Contracts\StorageServiceInterface;
use App\Helpers\StorageUrlHelper;
use App\Models\BlockchainTransaction;
use App\Models\User;
use App\Models\Course;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class CertificateService {
    protected $certificateRepository;
    protected StorageServiceInterface $storage;
    
    public function __construct(
        CertificateRepository $certificateRepository,
        StorageServiceInterface $storage
    ) { 
        $this->certificateRepository = $certificateRepository;
        $this->storage = $storage;
    }
    
    public function getMyCertificates($studentId) {
        return $this->certificateRepository->getByStudentId($studentId);
    }
    
    public function generatePdfCertificate(User $student, Course $course, $finalScore, $certificateNumber) {
        try {
            // Validate inputs
            if (!$student || !$course || !$certificateNumber) {
                throw new \Exception("Missing required data for certificate generation");
            }

            // Prepare data for PDF template
            $pdfData = [
                'student' => ($student->first_name ?? '') . ' ' . ($student->last_name ?? ''),
                'course' => $course->title ?? 'Unknown Course',
                'score' => $finalScore,
                'issue_date' => now()->format('F d, Y'),
                'certificate_number' => $certificateNumber,
                'bg_image' => 'data:image/png;base64,' . base64_encode(file_get_contents(public_path('cert_assets/certificate_bg.png'))),
                'logo_image' => 'data:image/svg+xml;base64,' . base64_encode(file_get_contents(public_path('cert_assets/certchain_logo.svg'))),
                'badge_image' => 'data:image/png;base64,' . base64_encode(file_get_contents(public_path('cert_assets/award_badge.png')))
            ];
            
            Log::info("Generating PDF certificate for student: {$student->id}, course: {$course->id}, cert: {$certificateNumber}");
            
            // Generate PDF
            $pdf = Pdf::loadView('certificates.template', $pdfData);
            $pdf->setPaper('A4', 'landscape');
            
            // Get PDF content
            $pdfContent = $pdf->output();
            
            if (!$pdfContent || strlen($pdfContent) < 1000) {
                throw new \Exception("PDF content is empty or too small");
            }
            
            Log::info("PDF content generated, size: " . strlen($pdfContent) . " bytes");
            
            // Save PDF to storage with proper content type
            $pdfPath = "certificates/{$certificateNumber}.pdf";
            
            $uploaded = $this->storage->put($pdfPath, $pdfContent, [
                'ContentType' => 'application/pdf',
                'CacheControl' => 'max-age=31536000',
                'visibility' => 'public'
            ]);
            
            if (!$uploaded) {
                throw new \Exception("Failed to upload PDF to storage");
            }
            
            Log::info("PDF uploaded successfully to S3: {$pdfPath}");
            
            // Verify the upload
            if (!$this->storage->exists($pdfPath)) {
                throw new \Exception("PDF file not found in storage after upload");
            }
            
            // Generate storage URL
            $pdfUrl = StorageUrlHelper::buildFullUrl($pdfPath);
            
            // Generate hash for blockchain verification
            $pdfHash = hash('sha256', $pdfContent);
            
            Log::info("PDF URL generated: {$pdfUrl}");
            
            $result = [
                'pdf_url' => $pdfUrl,
                'pdf_hash' => $pdfHash,
                'pdf_path' => $pdfPath
            ];
            
            // Validate result before returning
            if (empty($result['pdf_url']) || empty($result['pdf_hash'])) {
                throw new \Exception("Invalid result data");
            }
            
            return $result;
            
        } catch (\Exception $e) {
            Log::error("Certificate PDF generation failed: " . $e->getMessage(), [
                'student_id' => $student->id ?? null,
                'course_id' => $course->id ?? null,
                'certificate_number' => $certificateNumber,
                'trace' => $e->getTraceAsString()
            ]);
            throw new \Exception("Failed to generate certificate: " . $e->getMessage());
        }
    }
    
    public function issueCertificate($studentId, $courseId, $finalScore = 100) {
        $exists = $this->certificateRepository->getByStudentAndCourse($studentId, $courseId);
        if ($exists) throw new \Exception('Certificate already issued');
        $certNumber = 'CERT-' . now()->format('Y') . '-' . strtoupper(Str::random(6));
        $data = ['id' => Str::uuid(), 'certificate_number' => $certNumber, 'student_id' => $studentId, 'course_id' => $courseId, 'status' => 'ISSUED', 'final_score' => $finalScore, 'issued_at' => now()];
        return $this->certificateRepository->create($data);
    }
    
    public function getCertificateById($id) {
        return $this->certificateRepository->getById($id);
    }
    
    public function verifyCertificate($certNumber) {
        $cert = $this->certificateRepository->getByCertificateNumber($certNumber);
        if (!$cert || $cert->status !== 'ISSUED') throw new \Exception('Invalid certificate');
        return $cert;
    }
    
    public function revokeCertificate($certId, $reason = null) {
        return $this->certificateRepository->update($certId, ['status' => 'REVOKED', 'revoked_at' => now(), 'revocation_reason' => $reason]);
    }
    
    public function attachBlockchain($certId, array $data) {
        $tx = BlockchainTransaction::create(['id' => Str::uuid(), 'transaction_hash' => $data['transaction_hash'], 'network' => $data['network'], 'certificate_hash' => $data['certificate_hash'], 'metadata' => json_encode($data['metadata'] ?? []), 'status' => 'CONFIRMED', 'certificate_id' => $certId, 'confirmed_at' => now()]);
        return $tx;
    }
}