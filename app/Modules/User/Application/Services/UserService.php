<?php

namespace App\Modules\User\Application\Services;

use App\Modules\User\Domain\Repositories\IUserRepository;
use Illuminate\Support\Facades\Hash;

class UserService
{
    protected $userRepository;

    public function __construct(IUserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function createUser(array $data)
    {
        $data['password'] = Hash::make($data['password']);
        return $this->userRepository->create($data);
    }

    public function updateUser($id, array $data)
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }
        return $this->userRepository->update($id, $data);
    }

    public function deleteUser($id)
    {
        return $this->userRepository->delete($id);
    }

    public function findUserByEmail($email)
    {
        return $this->userRepository->findByEmail($email);
    }

    public function findUsersByRole($role)
    {
        return $this->userRepository->findByRole($role);
    }

    public function assignRole($userId, $roleId)
    {
        return $this->userRepository->attachRole($userId, $roleId);
    }

    public function removeRole($userId, $roleId)
    {
        return $this->userRepository->detachRole($userId, $roleId);
    }
} 