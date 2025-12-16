<?php

namespace App\Services;

use App\Models\Answer;
use App\Models\Quiz;
use App\Models\QuizResult;

class QuizGradingService
{
  
    public function gradeQuiz(int $userId, Quiz $quiz, array $userAnswers): QuizResult
    {
        $quiz->load('questions');
        
        $totalQuestions = $quiz->questions->count();
        $correctAnswers = $this->calculateCorrectAnswers($userAnswers);
        $score = $this->calculateScore($correctAnswers, $totalQuestions);

        // Create quiz result record
        $result = QuizResult::create([
            'user_id' => $userId,
            'quiz_id' => $quiz->id,
            'score' => $score,
            'completed_at' => now(),
        ]);

        return $result;
    }

   
    private function calculateCorrectAnswers(array $userAnswers): int
    {
        $correctCount = 0;

        foreach ($userAnswers as $userAnswer) {
            $answer = Answer::find($userAnswer['answer_id']);

            // Validate: answer belongs to the question and is correct
            if ($answer && 
                $answer->question_id == $userAnswer['question_id'] && 
                $answer->is_correct) {
                $correctCount++;
            }
        }

        return $correctCount;
    }

    
    private function calculateScore(int $correct, int $total): int
    {
        if ($total === 0) {
            return 0;
        }

        return (int) round(($correct / $total) * 100);
    }
}
