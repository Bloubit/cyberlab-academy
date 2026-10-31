<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

abstract class BaseRepository
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function all()
    {
        return $this->model->all();
    }

    public function find($id)
    {
        return $this->model->find($id);
    }

    public function findOrFail($id)
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(Model $model, array $data)
    {
        $model->update($data);
        return $model->fresh();
    }

    public function delete(Model $model)
    {
        return $model->delete();
    }

    public function query(): Builder
    {
        return $this->model->query();
    }

    public function where(string $column, $value): Builder
    {
        return $this->model->where($column, $value);
    }

    public function whereIn(string $column, array $values): Builder
    {
        return $this->model->whereIn($column, $values);
    }

    public function count(): int
    {
        return $this->model->count();
    }

    public function paginate(int $perPage = 15)
    {
        return $this->model->paginate($perPage);
    }
}
