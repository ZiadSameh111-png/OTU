<?php

namespace App\Modules\User;

use App\Modules\User\Domain\Repositories\IUserRepository;
use App\Modules\User\Infrastructure\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

class UserServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(IUserRepository::class, UserRepository::class);
    }

    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/Infrastructure/Database/Migrations');
        $this->loadRoutesFrom(__DIR__ . '/Infrastructure/Routes/api.php');
        $this->loadRoutesFrom(__DIR__ . '/Infrastructure/Routes/web.php');
    }
} 