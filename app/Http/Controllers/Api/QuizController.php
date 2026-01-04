<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuizController extends Controller
{
    // 1. LIHAT SEMUA KUIS DI KURSUS TERTENTU
    public function index(Request $request)
    {
        $request->validate(['course_id' => 'required|exists:courses,id']);

        // --- PERBAIKAN DISINI ---
        // Tambahkan ->withCount('questions')
        // Ini akan membuat field baru bernama 'questions_count' di JSON response
        $quizzes = Quiz::where('course_id', $request->course_id)
            ->withCount('questions') 
            ->get();

        return response()->json(['data' => $quizzes]);
    }

    // 2. BUAT KUIS BARU (Hanya Judul & Deskripsi)
    public function store(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        // Cek Kepemilikan Course
        $course = Course::find($request->course_id);
        
        // Pastikan user yang login adalah pemilik course
        if ($course->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $quiz = Quiz::create($request->all());

        return response()->json([
            'message' => 'Quiz created successfully',
            'data' => $quiz
        ], 201);
    }

    // 3. DETAIL KUIS (Load Soal & Jawaban)
    public function show($id)
    {
        // Mengambil Quiz + Questions + Answers (Nested Eager Loading)
        // Kita juga tambahkan withCount disini agar detailnya lengkap
        $quiz = Quiz::with('questions.answers')
            ->withCount('questions')
            ->find($id);

        if (!$quiz) {
            return response()->json(['message' => 'Quiz not found'], 404);
        }

        return response()->json(['data' => $quiz]);
    }

    // 4. HAPUS KUIS
    public function destroy($id)
    {
        $quiz = Quiz::find($id);
        if (!$quiz) {
            return response()->json(['message' => 'Quiz not found'], 404);
        }

        // Cek permission via Course -> User
        // Menggunakan optional() atau relasi untuk keamanan jika course terhapus
        if ($quiz->course && $quiz->course->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $quiz->delete();
        return response()->json(['message' => 'Quiz deleted successfully']);
    }
}