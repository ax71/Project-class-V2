<?php

namespace App\Policies;

use App\Models\Quiz;
use App\Models\User;

class QuizPolicy
{
    /**
     * Determine if the user can view any quizzes.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can view the quiz.
     */
    public function view(User $user, Quiz $quiz): bool
    {
        return true;
    }

    /**
     * Determine if the user can create quizzes.
     */
    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine if the user can update the quiz.
     */
    public function update(User $user, Quiz $quiz): bool
    {
        return $user->id === $quiz->course->user_id || $user->role === 'admin';
    }

    /**
     * Determine if the user can delete the quiz.
     */
    public function delete(User $user, Quiz $quiz): bool
    {
        return $user->id === $quiz->course->user_id || $user->role === 'admin';
    }
}
