<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Progress;
use App\Models\Material; // Pastikan import Model Material
use App\Models\Quiz;     // Pastikan import Model Quiz
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourseProgressController extends Controller
{
    // UPDATE PROGRESS
   public function update(Request $request)
{
    // 1. Validasi Input
    $request->validate([
        'course_id' => 'required|exists:courses,id',
        'material_id' => 'nullable|exists:materials,id',
        'quiz_id' => 'nullable|exists:quizzes,id',
        'is_completed' => 'required|boolean'
    ]);

    $user = Auth::user();
    $courseId = $request->course_id;

    // 2. Simpan Progress ITEM SPESIFIK (Granular)
    // Kita gunakan updateOrCreate agar tidak duplikat
    Progress::updateOrCreate(
        [
            'user_id' => $user->id,
            'course_id' => $courseId,
            'material_id' => $request->material_id, // Bisa null jika ini quiz
            'quiz_id' => $request->quiz_id,         // Bisa null jika ini material
        ],
        [
            'is_completed' => $request->is_completed
        ]
    );

    // 3. HITUNG ULANG PERSENTASE (Otomatisasi)
    // Hitung total item dalam course (Materi + Kuis)
    $totalMaterials = Material::where('course_id', $courseId)->count();
    $totalQuizzes = Quiz::where('course_id', $courseId)->count();
    $totalItems = $totalMaterials + $totalQuizzes;

    // Hitung item yang SUDAH diselesaikan user
    $completedItems = Progress::where('user_id', $user->id)
        ->where('course_id', $courseId)
        ->where('is_completed', true)
        ->where(function($q) {
            $q->whereNotNull('material_id')->orWhereNotNull('quiz_id');
        })
        ->count();

    // Kalkulasi Persen
    $percentage = ($totalItems > 0) ? round(($completedItems / $totalItems) * 100) : 0;

    // Opsional: Simpan persentase ke tabel 'enrollments' atau tabel progress summary jika ada
    // Enrollment::where('user_id', $user->id)->where('course_id', $courseId)->update(['progress' => $percentage]);

    return response()->json([
        'message' => 'Progress updated',
        'data' => [
            'percentage' => $percentage,
            'completed_items' => $completedItems,
            'total_items' => $totalItems
        ]
    ]);
}

    
  public function index(Request $request)
    {
        $userId = Auth::id();

        // 1. Ambil Progress User (Hanya ID-nya saja biar ringan)
        // Kita pakai 'select' agar tidak menarik data yang tidak perlu
        $completedItems = Progress::where('user_id', $userId)
            ->where('is_completed', true)
            ->select('course_id', 'id') 
            ->get();

        // 2. Kelompokkan progress berdasarkan Course ID
        $groupedByCourse = $completedItems->groupBy('course_id');

        // 3. OPTIMASI QUERY (Anti Lemot)
        // Ambil ID course yang terlibat
        $courseIds = $groupedByCourse->keys(); 
        
        // Ambil Data Course + Hitung Total Material/Quiz SEKALIGUS (1x Query)
        // Pastikan Model Course Anda punya relasi function materials() dan quizzes()
        $courses = Course::whereIn('id', $courseIds)
            ->withCount(['materials', 'quizzes']) // <--- INI KUNCINYA
            ->get()
            ->keyBy('id'); // Index array biar gampang diambil

        $result = [];

        foreach ($groupedByCourse as $courseId => $items) {
            // Ambil data course dari memori (Tanpa Query DB lagi!)
            $course = $courses[$courseId] ?? null;

            if (!$course) continue;

            // Ambil hitungan total langsung dari hasil withCount
            $totalMaterials = $course->materials_count ?? 0;
            $totalQuizzes = $course->quizzes_count ?? 0;
            $grandTotal = $totalMaterials + $totalQuizzes;

            // Hitung Item yang Selesai
            $completedCount = $items->count();

            // Hitung Persentase
            $percentage = $grandTotal > 0 ? round(($completedCount / $grandTotal) * 100) : 0;

            $result[] = [
                'course_id' => $courseId,
                'title' => $course->title,
                'percentage' => $percentage,
                'thumbnail' => $course->thumbnail, // Sesuaikan dengan nama kolom di DB
                'completed_items' => $completedCount,
                'total_items' => $grandTotal
            ];
        }

        return response()->json(['data' => $result]);
    }

    public function myProgress(Request $request)
{
    // Mengambil semua progress user yang sudah selesai
    $progress = Progress::where('user_id', Auth::id())
        ->where('is_completed', true)
        ->get(['course_id', 'material_id', 'quiz_id', 'is_completed']);

    return response()->json([
        'success' => true,
        'data' => $progress
    ]);
}
}