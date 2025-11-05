<?php namespace App\Services;
use App\Repositories\CertificateRepository;
use App\Models\BlockchainTransaction;
use Illuminate\Support\Str;

class CertificateService {
    protected $certificateRepository;
    public function __construct(CertificateRepository $certificateRepository) { $this->certificateRepository = $certificateRepository; }
    public function getMyCertificates($studentId) {
        return $this->certificateRepository->getByStudentId($studentId);
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