<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Material; 
use App\Models\Quiz;     
use App\Models\Progress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CertificateController extends Controller
{
    public function generate(Request $request)
    {
        $request->validate(['course_id' => 'required|exists:courses,id']);

        $user = Auth::user();
        $courseId = $request->course_id;

        // 1. Cek apakah sertifikat sudah pernah diklaim?
        $existingCert = Certificate::where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->first();
            
        if ($existingCert) {
            return response()->json([
                'message' => 'Certificate already issued',
                'data' => $existingCert
            ], 200);
        }
        
        // A. Hitung Total Materi + Quiz
        $totalItems = Material::where('course_id', $courseId)->count() 
                    + Quiz::where('course_id', $courseId)->count();

        // B. Hitung yang user sudah selesaikan
        $completedCount = Progress::where('user_id', $user->id)
                        ->where('course_id', $courseId)
                        ->where('is_completed', true)
                        ->count();

        // C. Hitung Persentase
        $percentage = $totalItems > 0 ? ($completedCount / $totalItems) * 100 : 0;

        // Syarat Lulus: Harus 100%
        if ($percentage < 100) {
            return response()->json([
                'message' => 'Cannot claim certificate. Your progress is only ' . round($percentage) . '%.'
            ], 403);
        }

        // 3. GENERATE SERTIFIKAT
        $code = 'CERT-' . date('Y') . '-' . strtoupper(Str::random(8));

        $certificate = Certificate::create([
            'user_id' => $user->id,
            'course_id' => $courseId,
            'certificate_code' => $code,
            'certificate_url' => url("/certificates/view/{$code}"),
            'issued_at' => now(),
        ]);

        return response()->json([
            'message' => 'Certificate issued successfully!',
            'data' => $certificate
        ], 201);
    }

    // LIHAT SERTIFIKAT SAYA
    public function index()
    {
        $certificates = Certificate::with(['course:id,title','user:id,name']) // Pastikan relasi course ada di Model Certificate
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return response()->json(['data' => $certificates]);
    }
}