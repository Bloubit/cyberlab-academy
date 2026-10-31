<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Quiz;

class QuizPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Quiz $quiz): bool
    {
        return $quiz->is_published || $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isInstructor() || $user->isAdmin();
    }

    public function update(User $user, Quiz $quiz): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Quiz $quiz): bool
    {
        return $user->isAdmin();
    }

    public function attempt(User $user, Quiz $quiz): bool
    {
        return $quiz->is_published && $quiz->canAttempt($user);
    }
}
