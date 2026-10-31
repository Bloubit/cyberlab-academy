<?php

namespace App\Repositories;

use App\Models\Course;

class CourseRepository extends BaseRepository
{
    public function __construct(Course $model)
    {
        parent::__construct($model);
    }

    public function getPublished()
    {
        return $this->model->published()->get();
    }

    public function getFeatured()
    {
        return $this->model->published()->featured()->get();
    }

    public function getByCategory(int $categoryId)
    {
        return $this->model->published()->byCategory($categoryId)->get();
    }

    public function getByInstructor(int $instructorId)
    {
        return $this->model->where('instructor_id', $instructorId)->get();
    }

    public function search(array $filters)
    {
        return $this->model->published()
            ->when($filters['category_id'] ?? null, fn($q, $v) => $q->byCategory($v))
            ->when($filters['difficulty'] ?? null, fn($q, $v) => $q->byDifficulty($v))
            ->when($filters['search'] ?? null, fn($q, $v) => $q->whereRaw('LOWER(title) LIKE ?', ['%' . mb_strtolower($v) . '%']))
            ->when($filters['is_free'] ?? null, fn($q, $v) => $q->where('is_free', $v))
            ->paginate($filters['per_page'] ?? 15);
    }
}
