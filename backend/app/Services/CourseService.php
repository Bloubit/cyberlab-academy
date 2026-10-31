<?php

namespace App\Services;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Module;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\User;
use Illuminate\Support\Str;

class CourseService
{
    /**
     * Create a new course.
     */
    public function create(array $data, User $instructor): Course
    {
        $course = Course::create([
            'category_id' => $data['category_id'],
            'instructor_id' => $instructor->id,
            'title' => $data['title'],
            'slug' => Course::generateSlug($data['title']),
            'subtitle' => $data['subtitle'] ?? null,
            'description' => $data['description'] ?? null,
            'difficulty_level' => $data['difficulty_level'] ?? 'beginner',
            'estimated_hours' => $data['estimated_hours'] ?? null,
            'price' => $data['price'] ?? 0,
            'currency' => $data['currency'] ?? 'USD',
            'is_free' => $data['is_free'] ?? false,
            'requirements' => $data['requirements'] ?? [],
            'learning_outcomes' => $data['learning_outcomes'] ?? [],
            'tags' => $data['tags'] ?? [],
        ]);

        return $course;
    }

    /**
     * Update course.
     */
    public function update(Course $course, array $data): Course
    {
        $course->update($data);
        return $course;
    }

    /**
     * Delete course.
     */
    public function delete(Course $course): bool
    {
        return $course->delete();
    }

    /**
     * Publish course.
     */
    public function publish(Course $course): Course
    {
        $course->update([
            'is_published' => true,
            'published_at' => now(),
        ]);
        return $course;
    }

    /**
     * Unpublish course.
     */
    public function unpublish(Course $course): Course
    {
        $course->update([
            'is_published' => false,
            'published_at' => null,
        ]);
        return $course;
    }

    /**
     * Enroll user in course.
     */
    public function enroll(User $user, Course $course): CourseEnrollment
    {
        if ($course->isEnrolled($user)) {
            throw new \Exception('User is already enrolled in this course');
        }

        if ($course->enrollment_limit && $course->enrollment_count >= $course->enrollment_limit) {
            throw new \Exception('Course enrollment limit reached');
        }

        $enrollment = CourseEnrollment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'status' => 'active',
            'progress' => 0,
            'enrolled_at' => now(),
        ]);

        // Update course stats
        $course->update([
            'stats' => array_merge($course->stats ?? [], [
                'enrollments' => ($course->stats['enrollments'] ?? 0) + 1,
            ]),
        ]);

        return $enrollment;
    }

    /**
     * Get user's enrolled courses.
     */
    public function getEnrolledCourses(User $user): \Illuminate\Database\Eloquent\Collection
    {
        return $user->enrolledCourses()
            ->with(['category', 'instructor'])
            ->get();
    }

    /**
     * Get course progress for user.
     */
    public function getProgress(User $user, Course $course): array
    {
        $enrollment = $course->getEnrollment($user);

        if (!$enrollment) {
            return ['enrolled' => false];
        }

        $totalLessons = $course->lessons()->count();
        $completedLessons = LessonProgress::where('user_id', $user->id)
            ->where('status', 'completed')
            ->whereIn('lesson_id', $course->lessons()->pluck('lessons.id'))
            ->count();

        $progress = $totalLessons > 0
            ? round(($completedLessons / $totalLessons) * 100, 2)
            : 0;

        // Update enrollment progress
        $enrollment->update(['progress' => $progress]);

        return [
            'enrolled' => true,
            'progress' => $progress,
            'total_lessons' => $totalLessons,
            'completed_lessons' => $completedLessons,
            'status' => $enrollment->status,
            'enrolled_at' => $enrollment->enrolled_at,
        ];
    }

    /**
     * Add module to course.
     */
    public function addModule(Course $course, array $data): Module
    {
        $maxOrder = $course->modules()->max('sort_order') ?? 0;

        return Module::create([
            'course_id' => $course->id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'sort_order' => $maxOrder + 1,
        ]);
    }

    /**
     * Add lesson to module.
     */
    public function addLesson(Module $module, array $data): Lesson
    {
        $maxOrder = $module->lessons()->max('sort_order') ?? 0;

        return Lesson::create([
            'module_id' => $module->id,
            'title' => $data['title'],
            'slug' => Str::slug($data['title']),
            'content_type' => $data['content_type'],
            'content' => $data['content'] ?? null,
            'sort_order' => $maxOrder + 1,
            'is_free' => $data['is_free'] ?? false,
            'duration_minutes' => $data['duration_minutes'] ?? null,
            'xp_reward' => $data['xp_reward'] ?? 10,
        ]);
    }

    /**
     * Search courses.
     */
    public function search(array $filters): \Illuminate\Database\Eloquent\Builder
    {
        $query = Course::published();

        if (isset($filters['category_id'])) {
            $query->byCategory($filters['category_id']);
        }

        if (isset($filters['difficulty'])) {
            $query->byDifficulty($filters['difficulty']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $search = $filters['search'];
                $q->whereRaw('LOWER(title) LIKE ?', ['%' . mb_strtolower($search) . '%'])
                  ->orWhereRaw('LOWER(description) LIKE ?', ['%' . mb_strtolower($search) . '%']);
            });
        }

        if (isset($filters['is_free'])) {
            $query->where('is_free', $filters['is_free']);
        }

        return $query;
    }
}
