<?php

namespace App\Repositories;

use App\Models\Lab;

class LabRepository extends BaseRepository
{
    public function __construct(Lab $model)
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

    public function getByDifficulty(string $difficulty)
    {
        return $this->model->published()->byDifficulty($difficulty)->get();
    }

    public function getByType(string $type)
    {
        return $this->model->published()->byType($type)->get();
    }

    public function search(array $filters)
    {
        return $this->model->published()
            ->when($filters['difficulty'] ?? null, fn($q, $v) => $q->byDifficulty($v))
            ->when($filters['lab_type'] ?? null, fn($q, $v) => $q->byType($v))
            ->when($filters['search'] ?? null, fn($q, $v) => $q->whereRaw('LOWER(title) LIKE ?', ['%' . mb_strtolower($v) . '%']))
            ->paginate($filters['per_page'] ?? 15);
    }
}
