<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository extends BaseRepository
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    public function findByUsername(string $username): ?User
    {
        return $this->model->where('username', $username)->first();
    }

    public function getActiveUsers()
    {
        return $this->model->where('is_active', true)->get();
    }

    public function getInstructors()
    {
        return $this->model->whereHas('roles', function ($query) {
            $query->where('slug', 'instructor');
        })->get();
    }

    public function search(string $query)
    {
        return $this->model->where(function ($q) use ($query) {
            $lower = mb_strtolower($query);
            $q->whereRaw('LOWER(name) LIKE ?', ['%' . $lower . '%'])
              ->orWhereRaw('LOWER(email) LIKE ?', ['%' . $lower . '%'])
              ->orWhereRaw('LOWER(username) LIKE ?', ['%' . $lower . '%']);
        })->paginate(15);
    }
}
