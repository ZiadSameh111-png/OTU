<?php

namespace App\Modules\User\Domain\Repositories;

use App\Core\Application\Interfaces\IRepository;

interface IUserRepository extends IRepository
{
    public function findByEmail(string $email);
    public function findByRole(string $role);
    public function attachRole($userId, $roleId);
    public function detachRole($userId, $roleId);
} 