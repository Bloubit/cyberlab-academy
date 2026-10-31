<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\CertificateVerification;
use App\Models\User;
use App\Models\Course;
use App\Models\Lab;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Barryvdh\DomPDF\Facade\Pdf;

class CertificateService
{
    /**
     * Generate certificate for course completion.
     */
    public function generateForCourse(User $user, Course $course): Certificate
    {
        // Check if already has certificate
        $existing = Certificate::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->where('revoked', false)
            ->first();

        if ($existing) {
            return $existing;
        }

        $certificate = Certificate::createForCourse($user, $course);

        // Generate QR code
        $this->generateQrCode($certificate);

        // Generate PDF
        $this->generatePdf($certificate);

        return $certificate;
    }

    /**
     * Generate certificate for lab completion.
     */
    public function generateForLab(User $user, Lab $lab): Certificate
    {
        $existing = Certificate::where('user_id', $user->id)
            ->where('lab_id', $lab->id)
            ->where('revoked', false)
            ->first();

        if ($existing) {
            return $existing;
        }

        $certificate = Certificate::createForLab($user, $lab);

        $this->generateQrCode($certificate);
        $this->generatePdf($certificate);

        return $certificate;
    }

    /**
     * Verify certificate.
     */
    public function verify(string $certificateNumber): array
    {
        $certificate = Certificate::where('certificate_number', $certificateNumber)
            ->with(['user', 'course', 'lab'])
            ->first();

        if (!$certificate) {
            return [
                'valid' => false,
                'message' => 'Certificate not found',
            ];
        }

        $verification = $certificate->verify();

        // Log verification
        CertificateVerification::logVerification(
            $certificate,
            'manual',
            $verification['valid'] ? 'valid' : 'invalid'
        );

        return $verification;
    }

    /**
     * Revoke certificate.
     */
    public function revoke(Certificate $certificate, string $reason = null): Certificate
    {
        $certificate->revoke($reason);
        return $certificate;
    }

    /**
     * Generate QR code for certificate.
     */
    protected function generateQrCode(Certificate $certificate): void
    {
        $qrCode = QrCode::size(200)
            ->generate($certificate->verification_url);

        $path = "certificates/{$certificate->certificate_number}/qr.png";
        \Storage::disk('public')->put($path, $qrCode);

        $certificate->update([
            'qr_code' => asset("storage/{$path}"),
        ]);
    }

    /**
     * Generate PDF for certificate.
     */
    protected function generatePdf(Certificate $certificate): void
    {
        $pdf = Pdf::loadView('certificates.template', [
            'certificate' => $certificate,
            'user' => $certificate->user,
            'course' => $certificate->course,
            'lab' => $certificate->lab,
        ]);

        $path = "certificates/{$certificate->certificate_number}/certificate.pdf";
        \Storage::disk('public')->put($path, $pdf->output());

        $certificate->update([
            'pdf_url' => asset("storage/{$path}"),
        ]);
    }

    /**
     * Get user's certificates.
     */
    public function getUserCertificates(User $user): \Illuminate\Database\Eloquent\Collection
    {
        return Certificate::where('user_id', $user->id)
            ->with(['course', 'lab'])
            ->orderByDesc('issued_at')
            ->get();
    }
}
