<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Course;

class CoursePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Course $course): bool
    {
        return $course->is_published || $course->instructor_id === $user->id || $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isInstructor() || $user->isAdmin();
    }

    public function update(User $user, Course $course): bool
    {
        return $course->instructor_id === $user->id || $user->isAdmin();
    }

    public function delete(User $user, Course $course): bool
    {
        return $course->instructor_id === $user->id || $user->isAdmin();
    }

    public function publish(User $user, Course $course): bool
    {
        return $course->instructor_id === $user->id || $user->isAdmin();
    }

    public function enroll(User $user, Course $course): bool
    {
        return $course->is_published && !$course->isEnrolled($user);
    }
}
