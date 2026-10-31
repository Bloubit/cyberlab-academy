<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Lab;

class LabPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Lab $lab): bool
    {
        return $lab->is_published || $lab->creator_id === $user->id || $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isInstructor() || $user->isAdmin();
    }

    public function update(User $user, Lab $lab): bool
    {
        return $lab->creator_id === $user->id || $user->isAdmin();
    }

    public function delete(User $user, Lab $lab): bool
    {
        return $lab->creator_id === $user->id || $user->isAdmin();
    }

    public function start(User $user, Lab $lab): bool
    {
        if (!$lab->is_published) {
            return false;
        }

        if ($lab->requires_subscription && !$user->hasActiveSubscription()) {
            return false;
        }

        return true;
    }
}
