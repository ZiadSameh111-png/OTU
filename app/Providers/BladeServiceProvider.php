<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use App\Helpers\GradeHelper;

class BladeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Grade Badge Color Directive
        Blade::directive('gradeBadgeColor', function ($grade) {
            return "<?php echo \App\Helpers\GradeHelper::getGradeBadgeColor($grade); ?>";
        });

        // GPA Badge Color Directive
        Blade::directive('gpaBadgeColor', function ($gpa) {
            return "<?php echo \App\Helpers\GradeHelper::getGpaBadgeColor($gpa); ?>";
        });

        // GPA Text Directive
        Blade::directive('gpaText', function ($gpa) {
            return "<?php echo \App\Helpers\GradeHelper::getGpaText($gpa); ?>";
        });
    }
} 