<?php

namespace App\Modules\User\Infrastructure\Repositories;

use App\Core\Infrastructure\Repositories\BaseRepository;
use App\Modules\User\Domain\Models\User;
use App\Modules\User\Domain\Repositories\IUserRepository;

class UserRepository extends BaseRepository implements IUserRepository
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function findByEmail(string $email)
    {
        return $this->model->where('email', $email)->first();
    }

    public function findByRole(string $role)
    {
        return $this->model->whereHas('roles', function($query) use ($role) {
            $query->where('name', $role);
        })->get();
    }

    public function attachRole($userId, $roleId)
    {
        $user = $this->getById($userId);
        if ($user) {
            return $user->roles()->attach($roleId);
        }
        return false;
    }

    public function detachRole($userId, $roleId)
    {
        $user = $this->getById($userId);
        if ($user) {
            return $user->roles()->detach($roleId);
        }
        return false;
    }
} 