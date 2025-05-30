<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use App\Helpers\ScoreHelper;

class HelperServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Register the getScoreColor directive
        Blade::directive('scoreColor', function ($score) {
            return "<?php echo \App\Helpers\ScoreHelper::getScoreColor($score); ?>";
        });

        // Register the getPassRateColor directive
        Blade::directive('passRateColor', function ($rate) {
            return "<?php echo \App\Helpers\ScoreHelper::getPassRateColor($rate); ?>";
        });
    }
} 