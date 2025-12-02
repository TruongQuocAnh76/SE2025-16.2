<?php 

namespace App\Services;

use App\Repositories\CertificateRepository;
use App\Models\BlockchainTransaction;
use App\Models\User;
use App\Models\Course;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class CertificateService {
    protected $certificateRepository;
    
    public function __construct(CertificateRepository $certificateRepository) { 
        $this->certificateRepository = $certificateRepository; 
    }
    
    public function getMyCertificates($studentId) {
        return $this->certificateRepository->getByStudentId($studentId);
    }
    
    public function generatePdfCertificate(User $student, Course $course, $finalScore, $certificateNumber) {
        // Prepare data for PDF template
        $pdfData = [
            'student' => $student->first_name . ' ' . $student->last_name,
            'course' => $course->title,
            'score' => $finalScore,
            'issue_date' => now()->format('F d, Y'),
            'certificate_number' => $certificateNumber
        ];
        
        // Generate PDF
        $pdf = Pdf::loadView('certificates.template', $pdfData);
        $pdf->setPaper('A4', 'landscape');
        
        // Save PDF to storage
        $pdfPath = "certificates/{$certificateNumber}.pdf";
        Storage::disk('public')->put($pdfPath, $pdf->output());
        
        // Get PDF URL
        $pdfUrl = Storage::disk('public')->url($pdfPath);
        
        // Generate hash for blockchain verification
        $pdfContent = file_get_contents(storage_path("app/public/{$pdfPath}"));
        $pdfHash = hash('sha256', $pdfContent);
        
        return [
            'pdf_url' => $pdfUrl,
            'pdf_hash' => $pdfHash,
            'pdf_path' => $pdfPath
        ];
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