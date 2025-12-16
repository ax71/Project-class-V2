<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\MaterialController;
use App\Http\Controllers\Api\QuizController;
use App\Http\Controllers\Api\QuestionController;
use App\Http\Controllers\Api\QuizResultController;
use App\Http\Controllers\Api\CourseProgressController;
use App\Http\Controllers\Api\CertificateController;

Route::middleware('throttle:5,1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    // Endpoint User (Pastikan UserResource ada, atau hapus 'new ...' jika belum ada)
    Route::get('/user', function (Request $request) {
        return response()->json([
            'success' => true,
            'data' => new \App\Http\Resources\UserResource($request->user())
        ]);
    });

    Route::apiResource('courses', CourseController::class);
    Route::delete('/courses/{course}/cover-image', [CourseController::class, 'deleteCoverImage']);

    Route::get('/materials', [MaterialController::class, 'index']);
    Route::post('/materials', [MaterialController::class, 'store']);
    Route::delete('/materials/{material}', [MaterialController::class, 'destroy']);

    Route::apiResource('quizzes', QuizController::class);

    Route::post('/questions', [QuestionController::class, 'store']);
    Route::delete('/questions/{id}', [QuestionController::class, 'destroy']);

    Route::post('/submit-quiz', [QuizResultController::class, 'store']);
    Route::get('/my-results', [QuizResultController::class, 'index']);
    
    // --- PERBAIKAN PROGRESS ---
    Route::post('/update-progress', [CourseProgressController::class, 'update']);
    
    // Route ini untuk data checklist hijau (Granular)
    Route::get('/my-progress', [CourseProgressController::class, 'myProgress']); 
    
    // Route tambahan jika Anda butuh ringkasan per kursus (Summary)
    Route::get('/progress-summary', [CourseProgressController::class, 'index']);

    Route::get('/my-certificates', [CertificateController::class, 'index']);
    Route::post('/my-certificates/generate', [CertificateController::class, 'generate']);

});