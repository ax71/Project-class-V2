<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\Progress;
use Illuminate\Support\Str;

class CertificateService
{
    /**
     * Generate and issue a certificate for a user completing a course.
     *
     * @param int $userId
     * @param int $courseId
     * @return Certificate|null
     */
    public function issueCertificate(int $userId, int $courseId): ?Certificate
    {
        // Check if certificate already exists
        $existingCert = Certificate::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->first();

        if ($existingCert) {
            return $existingCert;
        }

        // Check if user has completed the course
        if (!$this->hasCompletedCourse($userId, $courseId)) {
            return null;
        }

        // Generate certificate
        $code = $this->generateCertificateCode();

        return Certificate::create([
            'user_id' => $userId,
            'course_id' => $courseId,
            'certificate_code' => $code,
            'certificate_url' => url("/certificates/view/{$code}"),
            'issued_at' => now(),
        ]);
    }

    /**
     * Check if user has completed the course.
     *
     * @param int $userId
     * @param int $courseId
     * @return bool
     */
    private function hasCompletedCourse(int $userId, int $courseId): bool
    {
        $progress = Progress::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->where('status', 'complete')
            ->first();

        return $progress !== null;
    }

    /**
     * Generate a unique certificate code.
     *
     * @return string
     */
    private function generateCertificateCode(): string
    {
        return 'CERT-' . date('Y') . '-' . strtoupper(Str::random(8));
    }
}
