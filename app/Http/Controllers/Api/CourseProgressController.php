<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Progress;
use App\Models\Material; 
use App\Models\Quiz;     
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CourseProgressController extends Controller
{
    public function update(Request $request)
    {
        // Validasi Input
        $request->validate([
            'course_id'    => 'required|exists:courses,id',
            'material_id'  => 'nullable|exists:materials,id',
            'quiz_id'      => 'nullable|exists:quizzes,id',
            'is_completed' => 'required|boolean'
        ]);

        $user = Auth::user();
        $courseId = $request->course_id;

        // [FIX SECURITY] Validasi Relasi: Pastikan Material milik Course tersebut
        if ($request->material_id) {
            $isValid = Material::where('id', $request->material_id)
                               ->where('course_id', $courseId)
                               ->exists();
            if (!$isValid) return response()->json(['message' => 'Material tidak valid untuk course ini'], 400);
        }

        // [FIX SECURITY] Validasi Relasi: Pastikan Quiz milik Course tersebut
        if ($request->quiz_id) {
            $isValid = Quiz::where('id', $request->quiz_id)
                           ->where('course_id', $courseId)
                           ->exists();
            if (!$isValid) return response()->json(['message' => 'Quiz tidak valid untuk course ini'], 400);
        }

        // Simpan Progress
        Progress::updateOrCreate(
            [
                'user_id'     => $user->id,
                'course_id'   => $courseId,
                'material_id' => $request->material_id, 
                'quiz_id'     => $request->quiz_id,
            ],
            [
                'is_completed' => $request->is_completed
            ]
        );

        // Hitung Persentase Real-time
        $totalItems = Material::where('course_id', $courseId)->count() + Quiz::where('course_id', $courseId)->count();
        
        $completedItems = Progress::where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->where('is_completed', true)
            ->count();

        // Hindari pembagian dengan nol
        $percentage = ($totalItems > 0) ? round(($completedItems / $totalItems) * 100) : 0;

        return response()->json([
            'message' => 'Progress updated',
            'data' => [
                'percentage' => $percentage,
                'completed_items' => $completedItems,
                'total_items' => $totalItems,
                'is_course_completed' => ($percentage == 100)
            ]
        ]);
    }

    public function index(Request $request)
    {
        $userId = Auth::id();

        // Cek dulu apakah ada progress
        $completedItems = Progress::where('user_id', $userId)
            ->where('is_completed', true)
            ->select('course_id', 'material_id', 'quiz_id', 'id') 
            ->get();

        if ($completedItems->isEmpty()) {
            return response()->json(['data' => []]);
        }

        $groupedByCourse = $completedItems->groupBy('course_id');
        
        // Ambil data course
        $courses = Course::whereIn('id', $groupedByCourse->keys())
            ->withCount(['materials', 'quizzes']) // Pastikan Model Course punya relasi ini
            ->get()
            ->keyBy('id');

        $result = [];

        foreach ($groupedByCourse as $courseId => $items) {
            $course = $courses[$courseId] ?? null;
            if (!$course) continue;

            $totalItems = ($course->materials_count ?? 0) + ($course->quizzes_count ?? 0);
            
            // Hitung unik (mencegah duplikat hitungan)
            $completedCount = $items->unique(function ($item) {
                return $item->material_id . '-' . $item->quiz_id;
            })->count();

            $percentage = $totalItems > 0 ? round(($completedCount / $totalItems) * 100) : 0;

            $result[] = [
                'course_id' => $courseId,
                'title' => $course->title,
                'percentage' => $percentage,
                'thumbnail' => $course->thumbnail, 
                'completed_items' => $completedCount,
                'total_items' => $totalItems
            ];
        }

        return response()->json(['data' => $result]);
    }

    public function getActivityChart(Request $request)
    {
        $user = Auth::user();
        $startDate = Carbon::now()->subDays(6)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        $activities = Progress::where('user_id', $user->id)
            ->where('is_completed', true)
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->selectRaw('DATE(updated_at) as date, COUNT(*) as count')
            ->groupByRaw('DATE(updated_at)') // [FIX] Gunakan groupByRaw agar aman di semua SQL
            ->orderBy('date', 'ASC')
            ->get()
            ->keyBy('date');

        return response()->json([
            'status' => 'success',
            'data' => $this->fillEmptyDates($activities, $startDate)
        ]);
    }

    public function getGlobalActivityChart(Request $request)
    {
        $startDate = Carbon::now()->subDays(6)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        $activities = Progress::where('is_completed', true)
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->selectRaw('DATE(updated_at) as date, COUNT(*) as count')
            ->groupByRaw('DATE(updated_at)') // [FIX] Gunakan groupByRaw
            ->orderBy('date', 'ASC')
            ->get()
            ->keyBy('date');

        return response()->json([
            'status' => 'success', 
            'data' => $this->fillEmptyDates($activities, $startDate)
        ]);
    }

    private function fillEmptyDates($activities, $startDate)
    {
        $chartData = [];
        $period = Carbon::parse($startDate);

        for ($i = 0; $i < 7; $i++) {
            $dateString = $period->format('Y-m-d');
            
            $chartData[] = [
                'date' => $period->format('d M'),
                'total' => isset($activities[$dateString]) ? $activities[$dateString]->count : 0
            ];

            $period->addDay();
        }
        return $chartData;
    }

    public function myProgress(Request $request)
    {
        $progress = Progress::where('user_id', Auth::id())
            ->where('is_completed', true)
            ->get(['course_id', 'material_id', 'quiz_id', 'is_completed']);

        return response()->json([
            'success' => true,
            'data' => $progress
        ]);
    }
}