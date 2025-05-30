<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;

class ModuleServiceProvider extends ServiceProvider
{
    public function register()
    {
        $modules = File::directories(app_path('Modules'));
        
        foreach ($modules as $module) {
            // Register routes
            $routesPath = $module . '/Routes';
            if (File::exists($routesPath)) {
                $files = File::files($routesPath);
                foreach ($files as $file) {
                    require $file->getPathname();
                }
            }

            // Register providers
            $providerPath = $module . '/Providers';
            if (File::exists($providerPath)) {
                $files = File::files($providerPath);
                foreach ($files as $file) {
                    $className = "\\App\\Modules\\" . basename($module) . "\\Providers\\" . pathinfo($file->getFilename(), PATHINFO_FILENAME);
                    if (class_exists($className)) {
                        $this->app->register($className);
                    }
                }
            }
        }
    }

    public function boot()
    {
        $modules = File::directories(app_path('Modules'));
        
        foreach ($modules as $module) {
            // Load views
            $viewPath = $module . '/Views';
            if (File::exists($viewPath)) {
                $this->loadViewsFrom($viewPath, strtolower(basename($module)));
            }

            // Load migrations
            $migrationPath = $module . '/Database/Migrations';
            if (File::exists($migrationPath)) {
                $this->loadMigrationsFrom($migrationPath);
            }

            // Load translations
            $langPath = $module . '/Lang';
            if (File::exists($langPath)) {
                $this->loadTranslationsFrom($langPath, strtolower(basename($module)));
            }
        }
    }
} 