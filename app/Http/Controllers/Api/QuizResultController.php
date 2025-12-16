<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Quiz\SubmitQuizRequest;
use App\Models\Quiz;
use App\Services\QuizGradingService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\QuizResult;

class QuizResultController extends Controller
{
    use ApiResponse;

    protected QuizGradingService $gradingService;

    public function __construct(QuizGradingService $gradingService)
    {
        $this->gradingService = $gradingService;
    }

    /**
     * Submit quiz answers and get results.
     */
    public function store(SubmitQuizRequest $request): JsonResponse
    {
        $quiz = Quiz::with('questions')->findOrFail($request->input('quiz_id'));

        $result = $this->gradingService->gradeQuiz(
            auth()->id(),
            $quiz,
            $request->input('answers')
        );

        return $this->successResponse([
            'quiz_result_id' => $result->id,
            'score' => $result->score,
            'total_questions' => $quiz->questions->count(),
            'completed_at' => $result->completed_at->toISOString(),
        ], 'Quiz submitted successfully', 201);
    }

    /**
     * Get user's quiz results history.
     */
    public function index(Request $request): JsonResponse
    {
        $results = QuizResult::where('user_id', auth()->id())
            ->with('quiz:id,title,course_id')
            ->latest('completed_at')
            ->get()
            ->map(function ($result) {
                return [
                    'id' => $result->id,
                    'quiz' => [
                        'id' => $result->quiz->id,
                        'title' => $result->quiz->title,
                        'course_id' => $result->quiz->course_id,
                    ],
                    'score' => $result->score,
                    'completed_at' => $result->completed_at->toISOString(),
                ];
            });

        return $this->successResponse($results, 'Quiz results history');
    }
}
